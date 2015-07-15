module app.guest.login {

    export const namespace = 'app.guest.login';

    class LoginConfig {

        static $inject = ['ngJwtAuthServiceProvider'];
        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider){

            let config : NgJwtAuth.INgJwtAuthServiceConfig = {
                refreshBeforeSeconds: 60 * 10, //10 mins
                checkExpiryEverySeconds: 60, //1 min
            };

            ngJwtAuthServiceProvider.configure(config);

        }

    }

    class LoginInit {

        static $inject = ['ngJwtAuthService'];
        constructor(
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService
        ) {

            ngJwtAuthService.init(); //initialise the auth service (kicks off the timers etc)
        }

    }

    interface IScope extends ng.IScope
    {
    }

    class LoginController {

        static $inject = ['$scope'];
        constructor(private $scope : IScope) {

        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .run(LoginInit)
        .controller(namespace+'.controller', LoginController);

}