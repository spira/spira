

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


    });

});