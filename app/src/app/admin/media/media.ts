module app.admin.media {

    export const namespace = 'app.admin.media';

    export class MediaConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/media',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
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

        static $inject = ['allMedia'];
        constructor(public allMedia:any[]) {

        }

    }

    angular.module(namespace, [])
        .config(MediaConfig)
        .controller(namespace+'.controller', MediaController);

}