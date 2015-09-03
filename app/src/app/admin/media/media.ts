namespace app.admin.media {

    export const namespace = 'app.admin.media';

    export interface IMediaStateParams extends ng.ui.IStateParamsService {
        page:number;
    }

    const perPage = 12;

    export class MediaConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                url: '/media/{page:int}',
                params: {
                    page: 1
                },
                views: {
                    "main@app.admin": {
                        controller: namespace + '.controller',
                        controllerAs: 'MediaController',
                        templateUrl: 'templates/app/admin/media/media.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    imagesPaginator: (imageService:common.services.image.ImageService) => {
                        return imageService.getImagesPaginator().setCount(perPage);
                    },
                    initialImages: (imagesPaginator:common.services.pagination.Paginator, $stateParams:IMediaStateParams):ng.IPromise<common.models.Image[]> => {
                        return imagesPaginator.getPage($stateParams.page);
                    }
                },
                data: {
                    title: "Media",
                    icon: 'description',
                    navigation: true,
                    navigationGroup: 'cms',
                    sortAfter: app.admin.articles.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export interface IProgressBar {
        statusText: string;
        visible: boolean;
        mode: string;
        value: number;
    }

    export class MediaController {

        static $inject = ['imageService', 'imagesPaginator', 'initialImages', '$stateParams'];

        public progressBar:IProgressBar = {
            statusText: 'Saving...',
            visible: false,
            mode: 'query',
            value: 0
        };

        public images:common.models.Image[] = [];
        public pages:number[] = [];
        public currentPageIndex:number;
        public uploadImage:common.services.image.IImageUploadOptions;

        constructor(private imageService:common.services.image.ImageService,
                    private imagesPaginator:common.services.pagination.Paginator,
                    images:common.models.Image[],
                    public $stateParams:IMediaStateParams) {

            this.images = images; //initialise the first images

            this.pages = imagesPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        /**
         * Upload files
         * @param image
         */
        public uploadFiles(image:common.services.image.IImageUploadOptions):void {

            this.progressBar.visible = true;

            debugger;

            let onSuccess = (image:common.models.Image) => {
                console.log('image uploaded.', image);
                this.progressBar.visible = false;

                if (this.images.length >= perPage){
                    this.images.pop();
                }

                this.images.unshift(image);
                this.imagesPaginator.setCount(this.imagesPaginator.getCount() + 1);

            };

            let onNotify = (notification:common.services.image.IImageNotification) => {

                this.progressBar.statusText = notification.message;
                switch (notification.event) {
                    case 'cloudinary_upload':
                        this.progressBar.mode = 'determinate';
                        this.progressBar.value = notification.progressValue;
                        break;
                    default:
                        this.progressBar.mode = 'indeterminate';
                }
            };

            this.imageService.uploadImage(image)
                .then(onSuccess, null, onNotify)
                .catch(function (err) {
                    console.error(err);
                });

        }

    }

    angular.module(namespace, [])
        .config(MediaConfig)
        .controller(namespace + '.controller', MediaController);

}