angular.module('app.public.features.foundation', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.features.foundation', {
            url: '/foundation',
            views: {
                "featuresContent@app.public.features": {
                    controller: 'app.public.features.foundation.controller',
                    templateUrl: 'templates/app/public/features/foundation/foundation.tpl.html'
                }
            },
            resolve: /*@ngInject*/{

            },
            data: {
                title: "Foundation Framework",
                role: 'public',
                icon: 'question'
            }
        });
    })

    .controller('app.public.features.foundation.controller', function($scope, $state) {

        $scope.state = $state.current;

    })

;