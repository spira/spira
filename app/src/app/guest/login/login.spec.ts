
namespace app.guest.login {

    describe('Login', () => {

        let $q:ng.IQService,
            fixtures = {
                getLoginSuccessPromise(deferredCreds) {

                    let deferred = $q.defer();

                    deferredCreds.promise.then(null, null, (creds) => {
                        if (creds.password == 'fail') {
                            deferred.notify(new NgJwtAuth.NgJwtAuthCredentialsFailedException('error'));
                        }
                        else if (creds.password == 'differentemail') {
                            deferred.resolve({email:'foofoo'});
                        }
                        else {
                            deferred.resolve('success');
                            deferredCreds.resolve(creds);
                        }
                    });

                    return {
                        promise: deferred.promise,
                    };
                }
            };

        describe('Configuration', () => {

            let LoginController:LoginController,
                $scope:ng.IScope,
                $rootScope:ng.IRootScopeService,
                $timeout:ng.ITimeoutService,
                $mdDialog:ng.material.IDialogService,
                notificationService:common.services.notification.NotificationService,
                authService:NgJwtAuth.NgJwtAuthService,
                deferredCredentials:ng.IDeferred<any>,
                loginSuccess:{promise:ng.IPromise<any>}
                ;

            beforeEach(() => {

                module('app');

                inject(($controller, _$rootScope_, _ngJwtAuthService_, _$mdDialog_, _notificationService_, _$q_, _$timeout_) => {
                    $rootScope = _$rootScope_;
                    $scope = $rootScope.$new();
                    $timeout = _$timeout_;
                    $mdDialog = _$mdDialog_;
                    notificationService = _notificationService_;
                    authService = _ngJwtAuthService_;
                    $q = _$q_;
                    deferredCredentials = $q.defer();

                    loginSuccess = fixtures.getLoginSuccessPromise(deferredCredentials);

                    LoginController = $controller(app.guest.login.namespace + '.controller', {
                        $scope: $scope,
                        $mdDialog: $mdDialog,
                        deferredCredentials: deferredCredentials,
                        loginSuccess: loginSuccess,
                    });
                });

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

            it('should be a valid controller', () => {

                expect(LoginController).to.be.ok;
            });

            it('should have initialised the auth service', () => {

                $scope.$apply();
                expect((<any>authService).refreshTimerPromise).to.be.ok;

            });

            describe('dialog interactions - valid login', () => {

                it('should cancel dialog when requested', () => { //@todo resolve why the cancel function is not firing the $mdDialog.show().catch() method

                    LoginController.cancelLoginDialog();

                    $timeout.flush(); //flush timeout as the modal is delayed

                    expect($mdDialog.cancel).to.have.been.called;
                    expect(deferredCredentials.promise).eventually.to.be.rejected;

                    $scope.$apply();

                    expect(authService.loggedIn).to.be.false;

                });

                it('should show the login dialog when prompted', () => {

                    authService.promptLogin();

                    $timeout.flush(); //flush timeout as the modal is delayed

                    expect($mdDialog.show).to.have.been.called;

                });

                it('should resolve the deferred credentials when valid login credentials are passed, then hide the dialog on success', function () {

                    let creds = {
                        username: 'foo',
                        password: 'bar',
                    };

                    LoginController.login(creds.username, creds.password);

                    expect(deferredCredentials.promise).eventually.to.become(creds);

                    expect(loginSuccess.promise).eventually.to.become('success');

                    $scope.$apply();

                    return loginSuccess.promise.then(() => {

                        expect($mdDialog.hide).to.have.been.called;

                    });

                });

                it('should show a please confirm email dialog when logged in with an unconfirmed email', function () {

                    let creds = {
                        username: 'foo',
                        password: 'differentemail',
                    };

                    LoginController.login(creds.username, creds.password);

                    expect(deferredCredentials.promise).eventually.to.become(creds);

                    expect(loginSuccess.promise).eventually.to.become({email:'foofoo'});

                    $scope.$apply();

                    return loginSuccess.promise.then(() => {

                        expect($mdDialog.show).to.have.been.called;

                    });

                });

                it('should cancel dialog and open reset password', () => {

                    LoginController.resetPassword();

                    $timeout.flush(); //flush timeout as the modal is delayed

                    expect($mdDialog.cancel).to.have.been.called;
                    expect(deferredCredentials.promise).eventually.to.be.rejected;

                    //check to see if the reset password dialog has been opened
                    expect($mdDialog.show).to.have.been.calledWith(sinon.match.has('controller', 'app.guest.resetPassword.controller'));

                });
            });

            describe('dialog interactions - invalid login', () => {

                let credsFail = {
                    username: 'foo',
                    password: 'fail',
                };

                let credsPass = {
                    username: 'foo',
                    password: 'pass',
                };

                it('should show an error when invalid login credentials are passed', () => {

                    var progressSpy = sinon.spy();
                    deferredCredentials.promise.then(null, null, progressSpy);

                    LoginController.login(credsFail.username, credsFail.password);
                    $scope.$apply();

                    LoginController.login(credsPass.username, credsPass.password);
                    $scope.$apply();

                    return loginSuccess.promise.then(function () {
                        progressSpy.should.have.been.calledWith(credsFail);
                        progressSpy.should.have.been.calledWith(credsPass);
                        progressSpy.should.have.been.calledTwice;
                        expect(notificationService.toast).to.have.been.calledOnce;
                    });

                });

                it('should only repeat the error message once for each failed credential attempt', () => {

                    var progressSpy = sinon.spy();
                    deferredCredentials.promise.then(null, null, progressSpy);

                    LoginController.login(credsFail.username, credsFail.password);
                    LoginController.login(credsFail.username, credsFail.password);
                    LoginController.login(credsPass.username, credsPass.password);

                    $scope.$apply();

                    return loginSuccess.promise.then(function () {
                        progressSpy.should.have.been.calledWith(credsFail);
                        progressSpy.should.have.been.calledWith(credsPass);
                        expect(notificationService.toast).to.have.been.calledTwice;
                    });

                })

            });

        });

    });

}