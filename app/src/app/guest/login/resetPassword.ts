module app.guest.resetPassword {

    export const namespace = 'app.guest.resetPassword';

    interface IScope extends ng.IScope
    {
        resetPassword(email):void;
        cancelResetPasswordDialog():void;
    }

    class ResetPasswordController {

        static $inject = ['$scope', '$rootScope', '$mdDialog', 'userService', '$mdToast'];

        constructor(
            private $scope:IScope,
            private $rootScope:global.IRootScope,
            private $mdDialog:ng.material.IDialogService,
            private userService:common.services.user.UserService,
            private $mdToast:ng.material.IToastService
        ) {
            $scope.resetPassword = (email) => {
                userService.resetPassword(email)
                    .then(() => {
                        $scope.cancelResetPasswordDialog();
                        this.$mdToast.show(
                            $mdToast.simple()
                                .hideDelay(2000)
                                .position('top')
                                .content('Password reset instructions have been sent to your email.')
                        );
                    })
                    .catch((error) => {
                        this.$mdToast.show(
                            (<any>$mdToast).simple() //<any> added so the parent method doesn't throw error, see https://github.com/borisyankov/DefinitelyTyped/issues/4843#issuecomment-124443371
                                .hideDelay(2000)
                                .position('top')
                                .content(error.data.message)
                                .parent('#resetPasswordDialog')
                        );
                    });
            };

            $scope.cancelResetPasswordDialog = () => {
                $mdDialog.cancel('closed');
            };
        }
    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', ResetPasswordController);
}