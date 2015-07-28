

describe('Login', () => {


    let $q:ng.IQService,
        fixtures = {
            getLoginSuccessPromise(deferredCreds){

                let deferred = $q.defer();

                deferredCreds.promise.then(null, null, (creds) => {
                    if (creds.password == 'fail'){
                        deferred.notify(new NgJwtAuth.NgJwtAuthException('error'));
                    }else{
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

        let LoginController:ng.IControllerService,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            $timeout:ng.ITimeoutService,
            $mdDialog:ng.material.IDialogService,
            $mdToast:ng.material.IToastService,
            authService:NgJwtAuth.NgJwtAuthService,
            deferredCredentials:ng.IDeferred<any>,
            loginSuccess:{promise:ng.IPromise<any>}
        ;

        beforeEach(() => {

            module('app');
        });


        beforeEach(()=> {

            inject(($controller, _$rootScope_, _ngJwtAuthService_, _$mdDialog_, _$mdToast_, _$q_, _$timeout_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $timeout = _$timeout_;
                $mdDialog = _$mdDialog_;
                $mdToast = _$mdToast_;
                authService = _ngJwtAuthService_;
                $q = _$q_;
                deferredCredentials = $q.defer();


                loginSuccess = fixtures.getLoginSuccessPromise(deferredCredentials);

                LoginController = $controller(app.guest.login.namespace+'.controller', {
                    $scope: $scope,
                    $mdDialog: $mdDialog,
                    deferredCredentials:deferredCredentials,
                    loginSuccess: loginSuccess,
                });
            })
        });

        beforeEach(() => {

            sinon.spy($mdDialog, 'hide');
            sinon.spy($mdDialog, 'cancel');
            sinon.spy($mdDialog, 'show');
            sinon.spy($mdToast, 'show');

        });

        afterEach(() => {

            (<any>$mdDialog).hide.restore();
            (<any>$mdDialog).cancel.restore();
            (<any>$mdDialog).show.restore();
            (<any>$mdToast).show.restore();

        });

        it('should be a valid controller', () => {

            expect(LoginController).to.be.ok;
        });

        it('should have initialised the auth service', () => {

            expect((<any>authService).refreshTimerPromise).to.be.ok;

        });

        describe('dialog interactions - valid login', () => {

            it('should cancel dialog when requested', () => { //@todo resolve why the cancel function is not firing the $mdDialog.show().catch() method


                (<any>$scope).cancelLoginDialog();

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

            it('should resolve the deferred credentials when valid login credentials are passed, then hide the dialog on success', function(){


                let creds = {
                    username: 'foo',
                    password: 'bar',
                };

                (<any>$scope).login(creds.username, creds.password);

                expect(deferredCredentials.promise).eventually.to.become(creds);

                expect(loginSuccess.promise).eventually.to.become('success');

                $scope.$apply();

                return loginSuccess.promise.then(() => {

                    expect($mdDialog.hide).to.have.been.called;

                });


            });

            it('should cancel dialog and open reset password', () => {

                (<any>$scope).resetPassword();

                $timeout.flush(); //flush timeout as the modal is delayed

                expect($mdDialog.cancel).to.have.been.called;
                expect(deferredCredentials.promise).eventually.to.be.rejected;

                //check to see if the reset password dialog has been opened
                expect($mdDialog.show).to.have.been.calledWithMatch(sinon.match.has('controller', 'app.guest.resetPassword.controller'));

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

                (<any>$scope).login(credsFail.username, credsFail.password);
                $scope.$apply();

                (<any>$scope).login(credsPass.username, credsPass.password);
                $scope.$apply();

                return loginSuccess.promise.then(function () {
                    progressSpy.should.have.been.calledWith(credsFail);
                    progressSpy.should.have.been.calledWith(credsPass);
                    progressSpy.should.have.been.calledTwice;
                    expect($mdToast.show).to.have.been.calledOnce;
                });

            });

            it('should only repeat the error message once for each failed credential attempt', () => {

                var progressSpy = sinon.spy();
                deferredCredentials.promise.then(null, null, progressSpy);

                (<any>$scope).login(credsFail.username, credsFail.password);
                (<any>$scope).login(credsFail.username, credsFail.password);
                (<any>$scope).login(credsPass.username, credsPass.password);

                $scope.$apply();

                return loginSuccess.promise.then(function () {
                    progressSpy.should.have.been.calledWith(credsFail);
                    progressSpy.should.have.been.calledWith(credsPass);
                    expect($mdToast.show).to.have.been.calledTwice;
                });

            })

        });


    });

});