module app.guest.error {

    const namespace = 'app.public.error';

    class ErrorConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:IState = {
                views: {
                    "main@app.public": {
                        controller: 'app.public.error.controller',
                        templateUrl: 'templates/app/public/error/error_template.tpl.html'
                    }
                },
                params: {
                    title: null,
                    message: null,
                    details: null,
                    errorType: null,
                    url: null,
                    method: null,
                },
                data: {
                    role: 'public',
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }
    }

    interface IErrorStateParams {
        title: string;
        details: any;
        url: string;
        method: string;
        message: string;
    }

    class ErrorInit {

        static $inject = ['ngRestAdapter', '$state', '$filter'];
        constructor(
            private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
            private $state:ng.ui.IStateService,
            private $filter:ng.IFilterService
        ) {

            ngRestAdapter.registerApiErrorHandler(_.bind(this.errorInterceptorHandler, this));

        }

        private errorInterceptorHandler = (requestConfig: ng.IRequestConfig, responseObject: ng.IHttpPromiseCallbackArg<any>) => {

            console.log('responseObject', responseObject);
            let params:IErrorStateParams = {
                title: responseObject.statusText,
                details: this.$filter('json')(responseObject),
                url: responseObject.config.url,
                method: responseObject.config.method,
                message: undefined,
            };

            if (responseObject.data && responseObject.data.message){
                params.message = responseObject.data.message;
            }

            this.$state.transitionTo('app.public.error', params);

        }

    }

    interface IScope extends ng.IScope
    {
        title: string;
        message: string;
        url: string;
        method: string;
        details?: string;
        goBack():void;
        reload():void;
    }

    class ErrorController {

        static $inject = ['$scope', 'ngRestAdapter', '$window', '$stateParams'];
        constructor(
            private $scope : IScope,
            private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
            private $window:ng.IWindowService,
            private $stateParams:IErrorStateParams
        ) {

            $scope.goBack = _.bind(this.goBack, this); //bind method to scope
            $scope.reload = _.bind(this.reload, this); //bind method to scope

            $scope.title = $stateParams.title;
            $scope.message = $stateParams.message;
            $scope.url = $stateParams.url;
            $scope.method = $stateParams.method;
            $scope.details = $stateParams.details;

        }

        public goBack = function() {
            this.$window.history.back();
        };

        public reload = function() {
            this.$window.location.reload();
        };

    }

    angular.module(namespace, [])
        .config(ErrorConfig)
        .run(ErrorInit)
        .controller(namespace+'.controller', ErrorController);

}
