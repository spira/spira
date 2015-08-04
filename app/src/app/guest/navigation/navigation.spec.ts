

describe('Navigation', () => {

    describe('Configuration', () => {

        let NavigationController:app.guest.navigation.NavigationController,
            $mdDialog:ng.material.IDialogService,
            authService:NgJwtAuth.NgJwtAuthService
        ;

        beforeEach(() => {
            module('app');
        });

        beforeEach(()=> {
            inject(($controller, $rootScope, _ngJwtAuthService_, _stateHelperService_, _$window_) => {
                authService = _ngJwtAuthService_;
                NavigationController = $controller(app.guest.navigation.namespace+'.controller', {
                    stateHelperService : _stateHelperService_,
                    $window : _$window_,
                    ngJwtAuthService : authService,
                });

            })
        });

        it('should be a valid controller', () => {

            expect(NavigationController).to.be.ok;
        });

        it('should have some navigation states', () => {

            expect(NavigationController.navigableStates).not.to.be.empty;

        });

        beforeEach(() => {

            sinon.spy(authService, 'promptLogin');
            sinon.spy(authService, 'logout');

        });

        it('should prompt for login', () => {

            NavigationController.promptLogin();

            expect(authService.promptLogin).to.have.been.calledOnce;

        });

        it('should logout', () => {

            NavigationController.logout();

            expect(authService.logout).to.have.been.calledOnce;

        });


    });

});