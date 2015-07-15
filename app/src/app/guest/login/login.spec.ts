

describe('Login', () => {

    describe('Configuration', () => {

        let LoginController:ng.IControllerService,
            $scope:ng.IScope,
            authService:NgJwtAuth.NgJwtAuthService;

        beforeEach(() => {

            module('app');
        });

        beforeEach(()=> {
            inject(($controller, $rootScope, _ngJwtAuthService_) => {
                $scope = $rootScope.$new();
                authService = _ngJwtAuthService_;
                LoginController = $controller(app.guest.login.namespace+'.controller', {$scope: $scope});
            })
        });

        it('should be a valid controller', () => {

            expect(LoginController).to.be.ok;
        });

        it('should have initialised the auth service', () => {

            expect((<any>authService).refreshTimerPromise).to.be.ok;

        })


    });

});