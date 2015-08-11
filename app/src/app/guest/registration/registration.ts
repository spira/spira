namespace app.guest.registration {

    export const namespace = 'app.guest.registration';

    export interface IScope extends ng.IScope {
        registerUser(email:string, password:string, first:string, last:string, goToProfile?:boolean);
        submitting:boolean;
    }

    export class RegistrationController {

        public isOpen = () => false;
        public socialLogin;

        public submitting:boolean = false;

        static $inject = ['$scope', 'userService', '$state', '$window', '$mdComponentRegistry', '$rootScope'];

        constructor(private $scope:IScope,
                    private userService:common.services.user.UserService,
                    private $state:ng.ui.IStateService,
                    private $window:ng.IWindowService,
                    private $mdComponentRegistry,
                    private $rootScope:global.IRootScope) {

            this.socialLogin = $rootScope.socialLogin;

            $scope.submitting = false;

            //the following is a hack to watch for the sidenav closing, see https://github.com/angular/material/issues/3179
            $mdComponentRegistry
                .when('registration')
                .then((sideNav)=> {
                    (<any>this).isOpen = angular.bind(sideNav, sideNav.isOpen);
                });

            $scope.$watch('RegistrationController.isOpen()', (isOpen, oldValue) => {

                if (!isOpen && oldValue) {
                    this.$rootScope.$broadcast('navigation', 'recalculateNavigableStateIndex');
                }

            }, true);

        }

        /**
         * Register a user
         * @param email
         * @param password
         * @param first
         * @param last
         * @param goToProfile
         * @returns {IPromise<TResult>}
         */
        public registerUser(email:string, password:string, first:string, last:string, goToProfile:boolean = false) {

            this.submitting = true;

            return this.userService.registerAndLogin(email, password, first, last)
                .then((createdUser) => {

                    if (goToProfile) {

                        this.$state.go('app.user.profile', {
                            onBoard: true //start the onboarding walkthrough
                        });
                    }

                    return createdUser;
                })
                .finally(() => {
                    this.submitting = false;
                })
                ;

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', RegistrationController);

}