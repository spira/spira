///<reference path="../../../../src/global.d.ts" />

module app.guest.sandbox {

    export const namespace = 'app.guest.sandbox';

    class SandboxConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/sandbox',
                views: {
                    "main@app.guest": {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/guest/sandbox/sandbox.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{

                },
                data: {
                    title: "Sandbox",
                    role: 'public',
                    icon: 'extension',
                    navigation: true,
                    sortAfter: app.guest.articles.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IScope extends ng.IScope
    {
        callApi(apiEndpoint:string):void;
        apiResult: any;
    }

    class SandboxController {

        static $inject = ['$scope', 'ngRestAdapter'];
        constructor(private $scope : IScope, private ngRestAdapter:NgRestAdapter.NgRestAdapterService) {

            $scope.callApi = _.bind(this.callApi, this); //bind method to scope

        }

        public callApi(apiEndpoint):void {

            this.ngRestAdapter
                //.skipInterceptor()
                .get(apiEndpoint)
                .then((result) => {
                    this.$scope.apiResult = result;
                })
            ;

        }
    }

    angular.module(namespace, [])
        .config(SandboxConfig)
        .controller(namespace+'.controller', SandboxController);

}
