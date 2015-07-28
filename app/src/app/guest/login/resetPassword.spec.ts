describe('ResetPassword', () => {

    describe('Configuration', () => {

        let ResetPasswordController:ng.IControllerService,
            $scope:ng.IScope,
            $mdDialog:ng.material.IDialogService,
            $timeout:ng.ITimeoutService,
            $rootScope:ng.IRootScopeService
            ;

        beforeEach(() => {
            module('app');
        });


        beforeEach(()=> {

            inject(($controller, _$rootScope_, _$mdDialog_, _$timeout_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $mdDialog = _$mdDialog_;
                $timeout = _$timeout_;

                ResetPasswordController = $controller(app.guest.resetPassword.namespace+'.controller', {
                    $scope: $scope,
                    $mdDialog: $mdDialog
                });
            })
        });

        beforeEach(() => {

            sinon.spy($mdDialog, 'hide');
            sinon.spy($mdDialog, 'cancel');
            sinon.spy($mdDialog, 'show');

        });

        afterEach(() => {

            (<any>$mdDialog).hide.restore();
            (<any>$mdDialog).cancel.restore();
            (<any>$mdDialog).show.restore();

        });

        describe.only('dialog interactions - reset password', () => {

            it('should cancel dialog when requested', () => {

                (<any>$scope).cancelResetPasswordDialog();

                $timeout.flush(); //flush timeout as the modal is delayed

                expect($mdDialog.cancel).to.have.been.called;

            });

            it('should do something', () => {

            });
        });
    });

});