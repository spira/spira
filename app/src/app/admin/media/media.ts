namespace app.admin.media {

    export const namespace = 'app.admin.media';

    export interface IMediaStateParams extends ng.ui.IStateParamsService {
        page:number;
    }

    export class MediaConfig {

        static $inject = ['stateHelperServiceProvider'];

        public static state:global.IState;

        constructor(private stateHelperServiceProvider) {

            MediaConfig.state = {
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
                    perPage: () => 12,
                    imagesPaginator: (imageService:common.services.image.ImageService, perPage:number) => {
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

            stateHelperServiceProvider.addState(namespace, MediaConfig.state);

        }

    }

    export interface IProgressBar {
        statusText: string;
        visible: boolean;
        mode: string;
        value: number;
    }

    export interface IImageConstraints {
        maxHeight:number;
        minHeight:number;
        maxWidth:number;
        minWidth:number;
        maxSize:string;
        minSize:string;
    }

    export class MediaController {

        static $inject = ['perPage', 'imageService', 'imagesPaginator', 'initialImages', '$stateParams'];

        public progressBar:IProgressBar = {
            statusText: 'Saving...',
            visible: false,
            mode: 'query',
            value: 0
        };

        public imageConstraints:IImageConstraints = {
            maxHeight:2500,
            minHeight:100,
            maxWidth:2500,
            minWidth:100,
            maxSize:'20MB',
            minSize:'10KB',
        };

        public pages:number[] = [];
        public currentPageIndex:number;
        public queuedImage:common.services.image.IImageUploadOptions;
        public imageUploadForm:ng.IFormController;

        constructor(private perPage:number,
                    private imageService:common.services.image.ImageService,
                    private imagesPaginator:common.services.pagination.Paginator,
                    public images:common.models.Image[],
                    public $stateParams:IMediaStateParams) {

            this.pages = imagesPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        /**
         * Upload files
         * @param image
         */
        public uploadImage(image:common.services.image.IImageUploadOptions):void {

            this.progressBar.visible = true;

            let onSuccess = (image:common.models.Image) => {

                this.progressBar.visible = false;

                if (this.images.length >= this.perPage) {
                    this.images.pop();
                }

                this.images.unshift(image);
                this.imagesPaginator.setCount(this.imagesPaginator.getCount() + 1);

                this.queuedImage = null;
                this.imageUploadForm.$setPristine();

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
                .then(onSuccess, null, onNotify);
        }

    }

    angular.module(namespace, [])
        .config(MediaConfig)
        .controller(namespace + '.controller', MediaController);

}