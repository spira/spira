namespace app.admin.media {

    export const namespace = 'app.admin.media';

    export class MediaConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                url: '/media',
                views: {
                    "main@app.admin": {
                        controller: namespace + '.controller',
                        controllerAs: 'MediaController',
                        templateUrl: 'templates/app/admin/media/media.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    allMedia: () => {
                        return [];
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

    export class MediaController {

        static $inject = ['allMedia', 'imageService'];

        constructor(public allMedia:any[], private imageService:common.services.image.ImageService) {

        }

        /**
         * Upload files
         * @param file
         */
        public uploadFiles(file:File):void {

            let onSuccess = (image:common.models.Image) => {
                console.log('image uploaded.', image);
            };

            let onNotify = (notification:common.services.image.IImageNotification) => {
                console.log('progress notification: ', notification);
            };

            this.imageService.uploadImage({
                file: file,
                alt: "test image",
            })
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