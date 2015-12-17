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
                        return imageService.getPaginator().setCount(perPage).noResultsResolve();
                    },
                    initialImages: (imagesPaginator:common.services.pagination.Paginator, $stateParams:IMediaStateParams):ng.IPromise<common.models.Image[]> => {
                        return imagesPaginator.getPage($stateParams.page);
                    }
                },
                data: {
                    title: "Media",
                    icon: 'image',
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

        public pages:number[] = [];
        public currentPageIndex:number;
        public imageUploadForm:ng.IFormController;
        public uploadedImage:common.models.Image;

        constructor(private perPage:number,
                    private imageService:common.services.image.ImageService,
                    private imagesPaginator:common.services.pagination.Paginator,
                    public images:common.models.Image[],
                    public $stateParams:IMediaStateParams) {

            this.pages = imagesPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        public imageUploaded(image:common.models.Image){

            if (this.images.length >= this.perPage) {
                this.images.pop();
            }

            this.images.unshift(image);
            this.imagesPaginator.setCount(this.imagesPaginator.getCount() + 1);

        }

    }

    angular.module(namespace, [])
        .config(MediaConfig)
        .controller(namespace + '.controller', MediaController);

}