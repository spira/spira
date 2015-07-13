///<reference path="../../../global.d.ts" />

module app.public.sandbox {

    const namespace = 'app.public.sandbox';

    class SandboxConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:ng.ui.IState = {
                url: '/sandbox',
                views: {
                    "main@app.public": {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/public/sandbox/sandbox.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{

                },
                data: {
                    title: "Sandbox",
                    role: 'public',
                    icon: 'home',
                    navigation: true
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export interface IScope extends ng.IScope
    {
        callApi(apiEndpoint:string):void;
        apiResult: any;
    }

    class SandboxController {

        static $inject = ['$scope', 'ngRestAdapter'];
        constructor(private $scope : IScope, private ngRestAdapter:NgRestAdapter.NgRestAdapterService) {

            $scope.callApi = _.bind(this.callApi, this); //bind method to scope

            console.log('ngRestAdapter uuid', ngRestAdapter.uuid());

        }

        public callApi(apiEndpoint):void {

            this.ngRestAdapter.get(apiEndpoint)
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
