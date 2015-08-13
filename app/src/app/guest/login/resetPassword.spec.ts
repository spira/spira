namespace app.guest.resetPassword {

    describe('ResetPassword', () => {

        let ResetPasswordController:ResetPasswordController,
            $scope:ng.IScope,
            $mdDialog:ng.material.IDialogService,
            $timeout:ng.ITimeoutService,
            $rootScope:ng.IRootScopeService,
            $q:ng.IQService,
            notificationService:common.services.notification.NotificationService,
            userService = {
                resetPassword: (email:string) => {
                    if (email == 'invalid@email.com') {
                        return $q.reject({data: {message: 'this failure message'}});
                    }
                    else {
                        return $q.when(true);
                    }
                }
            }
            ;

        beforeEach(() => {
            module('app');
        });

        beforeEach(()=> {

            inject(($controller, _$rootScope_, _$mdDialog_, _$timeout_, _$q_, _notificationService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $mdDialog = _$mdDialog_;
                $timeout = _$timeout_;
                notificationService = _notificationService_;
                $q = _$q_;

                ResetPasswordController = $controller(app.guest.resetPassword.namespace + '.controller', {
                    $mdDialog: $mdDialog,
                    userService: userService,
                    notificationService: notificationService,
                    defaultEmail: null,
                });
            })
        });

        beforeEach(() => {

            sinon.spy($mdDialog, 'hide');
            sinon.spy($mdDialog, 'cancel');
            sinon.spy($mdDialog, 'show');
            sinon.spy(notificationService, 'toast');

        });

        afterEach(() => {

            (<any>$mdDialog).hide.restore();
            (<any>$mdDialog).cancel.restore();
            (<any>$mdDialog).show.restore();
            (<any>notificationService).toast.restore();

        });

        describe('dialog interactions - reset password', () => {

            it('should cancel dialog when requested', () => {

                ResetPasswordController.cancelResetPasswordDialog();

                $timeout.flush(); // Flush timeout as the modal is delayed

                expect($mdDialog.cancel).to.have.been.called;

            });

            it('should display an error message when an invalid email is entered', () => {

                let email = 'invalid@email.com';

                ResetPasswordController.resetPassword(email);

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('this failure message');

            });

            it('should display a success message when a valid email is entered', () => {

                let email = 'valid@email.com';

                ResetPasswordController.resetPassword(email);

                $scope.$apply();

                expect(notificationService.toast).to.have.been.called.and.not.to.be.calledWith('this failure message');

            });
        });

    });

}