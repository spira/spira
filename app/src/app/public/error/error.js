

angular.module('app.public.error', [])

    .config(function(stateHelperServiceProvider, $httpProvider) {

        stateHelperServiceProvider.addState('app.public.error', {
            views: {
                "main@app.public": {
                    controller: 'app.public.error.controller',
                    templateUrl: 'public/error/error_template.tpl.html'
                }
            },
            params: {
                title: null,
                message: null,
                details: null,
                errorType: null,
                url: null,
                method: null
            }
        });

        $httpProvider.interceptors.push('errorInterceptor');

    })

    .factory('errorInterceptor',function($q, $rootScope, $injector) {
        return {
            'responseError': function(response) {

                var $state = $injector.get('$state'),
                    $filter = $injector.get('$filter'),
                    errors = {
                        400: {
                            title: '400 - Bad Request',
                            url: 'bad-request'
                        },
                        403: {
                            title: '403 - Access Forbidden',
                            url: 'forbidden'
                        },
                        404: {
                            title: '404 - Not Found',
                            url: 'not-found'
                        },
                        412: {
                            title: '412 - Precondition failed',
                            url: 'precondition-failed'
                        },
                        500: {
                            title: '500 - Internal Server Error',
                            url: 'internal-server-error'
                        },
                        502: {
                            title: '502 - Proxy Error',
                            url: 'proxy-server-error'
                        },
                        0: {
                            title: 'CORS Error - API Not Accepting Request',
                            url: 'cors-error'
                        }
                    };

                if (response.status in errors && !response.config.skipInterceptor) {

                    var params = {
                        errorType: errors[response.status].url,
                        title: errors[response.status].title,
                        details: $filter('json')(response),
                        url: response.config.url,
                        method: response.config.method
                    };

                    if (response.data && response.data.message){
                        params.message = response.data.message;
                    }

                    $state.transitionTo('app.public.error', params);
                }

                return $q.reject(response);
            }
        };
    })

    .controller('app.public.error.controller', function($rootScope, $scope, titleService, $stateParams, $state, $window, $filter) {

        titleService.setTitle($stateParams.title);

        $scope.title = $stateParams.title;
        $scope.message = $stateParams.message;
        $scope.url = $stateParams.url;
        $scope.method = $stateParams.method;

        if (!!$scope.details){
            $scope.details = _.isString($stateParams.details) ? $stateParams.details :  $filter('json')($stateParams.details);
        }

        $scope.goBack = function() {
            $window.history.back();
        };

        $scope.reload = function() {
            $window.location.reload();
        };

    })

;
