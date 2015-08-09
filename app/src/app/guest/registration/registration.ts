namespace app.guest.registration {

    export const namespace = 'app.guest.registration';

    export interface IScope extends ng.IScope {
        registerUser(email:string, password:string, first:string, last:string, goToProfile?:boolean);
        submitting:boolean;
    }

    class RegistrationController {

        static $inject = ['$scope', 'userService', '$state', '$window'];

        constructor(private $scope:IScope,
                    private userService:common.services.user.UserService,
                    private $state:ng.ui.IStateService,
                    private $window:ng.IWindowService) {

            $scope.submitting = false;

            $scope.registerUser = (email:string, password:string, first:string, last:string, goToProfile:boolean = false) => {

                $scope.submitting = true;

                return userService.registerAndLogin(email, password, first, last)
                    .then((createdUser) => {

                        if (goToProfile) {

                            this.$state.go('app.user.profile', {
                                onBoard: true //start the onboarding walkthrough
                            });
                        }

                        return createdUser;
                    })
                    .finally(() => {
                        $scope.submitting = false;
                    })
                    ;
            };

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', RegistrationController);

}