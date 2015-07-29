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
                        this.$mdToast.show({
                            hideDelay:2000,
                            position:'top',
                            template:'<md-toast>Password reset instructions have been sent to your email.</md-toast>'
                        });
                    })
                    .catch((error) => {
                        this.$mdToast.show({
                            hideDelay:2000,
                            position:'top',
                            parent:'#resetPasswordDialog',
                            template:'<md-toast>' + error.data.message + '</md-toast>'
                        });
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