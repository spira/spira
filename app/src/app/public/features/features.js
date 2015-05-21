angular.module('app.public.features', [
    'app.public.features.foundation',
    'app.public.features.api'
])

    .config(function(stateHelperServiceProvider) {

        stateHelperServiceProvider.addState('app.public.features', {
            url: '/features',
            views: {
                "main@app.public": {
                    controller: 'app.public.features.controller',
                    templateUrl: 'templates/app/public/features/features-layout.tpl.html'
                }
            },
            resolve: /*@ngInject*/{
                childStates: function($state, stateHelperService){

                    return stateHelperService.getChildStates('app.public.features');
                }
            },
            data: {
                title: "Features",
                icon: 'rocket',
                role: 'public'

            }
        });
    })

    .controller('app.public.features.controller', function($scope, childStates) {

        $scope.childStates = childStates;

    })

;