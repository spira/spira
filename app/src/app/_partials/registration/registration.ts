module app.partials.registration{

    export const namespace = 'app.partials.registration';

    export interface IScope extends ng.IScope
    {
        registerUser(email:string, password:string, first:string, last:string, goToProfile?:boolean)
    }

    class RegistrationController {

        static $inject = ['$scope', 'userService', '$state'];
        constructor(
            private $scope:IScope,
            private userService:common.services.UserService,
            private $state:ng.ui.IStateService
        ) {

            $scope.registerUser = (email:string, password:string, first:string, last:string, goToProfile:boolean = false) => {


                return userService.registerAndLogin(email, password, first, last)
                    .then((createdUser) => {

                        if (goToProfile){

                            this.$state.go('app.user.profile', {
                                onBoard: true //start the onboarding walkthrough
                            });
                        }

                        return createdUser;
                    })
                ;
            }

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', RegistrationController);


}