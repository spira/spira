

describe('Login', () => {


    let $q:ng.IQService,
        fixtures = {
            failLogin: false,
            getLoginSuccessPromise(fail){
                return {
                    promise: !fail ? $q.when('success') : $q.reject(new NgJwtAuth.NgJwtAuthException('error')),
                };
            }
        };

    describe('Configuration', () => {

        let LoginController:ng.IControllerService,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            $mdDialog:ng.material.IDialogService,
            authService:NgJwtAuth.NgJwtAuthService,
            deferredCredentials:ng.IDeferred<any>,
            loginSuccess:{promise:ng.IPromise<any>}
        ;

        beforeEach(() => {

            module('app');
        });


        beforeEach(()=> {

            inject(($controller, _$rootScope_, _ngJwtAuthService_, _$mdDialog_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $mdDialog = _$mdDialog_;
                authService = _ngJwtAuthService_;
                deferredCredentials = _$q_.defer();

                $q = _$q_;

                loginSuccess = fixtures.getLoginSuccessPromise(fixtures.failLogin);

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

        });

        afterEach(() => {

            (<any>$mdDialog).hide.restore();
            (<any>$mdDialog).cancel.restore();
            (<any>$mdDialog).show.restore();

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

                expect($mdDialog.cancel).to.have.been.called;
                expect(deferredCredentials.promise).eventually.to.be.rejected;

                $scope.$apply();
            });

            it('should show the login dialog when prompted', () => {


                authService.promptLogin();

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

            after(() => {

                fixtures.failLogin = true; //set up login failure for next describe block
            });


        });

        describe('dialog interactions - invalid login', () => {


            it('should show an error when invalid login credentials are passed', (done) => {

                let creds = {
                    username: 'foo',
                    password: 'bar',
                };

                (<any>$scope).login(creds.username, creds.password);

                expect(deferredCredentials.promise).eventually.to.become(creds);

                expect(loginSuccess.promise).eventually.to.be.rejectedWith('error');

                $scope.$apply();

                loginSuccess.promise.finally(() => {

                    expect((<any>$scope).loginError).to.have.length.above(0);

                    done();

                });


            });


        });


    });

});