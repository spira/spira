module app.partials.registration{

    export const namespace = 'app.partials.registration';

    export interface IScope extends ng.IScope
    {
        registerUser(email:string, password:string, first:string, last:string, goToProfile?:boolean);
        submitting:boolean;
        socialLogin(type:string, redirectState?:string, redirectStateParams?:Object);
    }

    class RegistrationController {

        static $inject = ['$scope', 'userService', '$state', '$window'];
        constructor(
            private $scope:IScope,
            private userService:common.services.UserService,
            private $state:ng.ui.IStateService,
            private $window:ng.IWindowService
        ) {

            $scope.submitting = false;

            $scope.registerUser = (email:string, password:string, first:string, last:string, goToProfile:boolean = false) => {

                $scope.submitting = true;

                return userService.registerAndLogin(email, password, first, last)
                    .then((createdUser) => {

                        if (goToProfile){

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


            $scope.socialLogin = (type:string, redirectState?:string, redirectStateParams:Object = {}) => {

                let url = '/auth/social/'+type;

                if (!_.isEmpty(redirectState)){
                    url += '?returnUrl='+(<any>this.$window).encodeURIComponent(this.$state.href(redirectState, redirectStateParams));
                }

                this.$window.location.href = url;

            }

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', RegistrationController);


}