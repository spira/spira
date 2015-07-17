

describe('Navigation', () => {

    describe('Configuration', () => {

        let NavigationController:ng.IControllerService,
            $scope:app.partials.navigation.IScope,
            $mdDialog:ng.material.IDialogService,
            authService:NgJwtAuth.NgJwtAuthService
        ;

        beforeEach(() => {
            module('app');
        });

        beforeEach(()=> {
            inject(($controller, $rootScope, _ngJwtAuthService_, _stateHelperService_, _$window_) => {
                $scope = $rootScope.$new();
                authService = _ngJwtAuthService_;
                NavigationController = $controller(app.partials.navigation.namespace+'.controller', {
                    $scope: $scope,
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

            expect($scope.navigationStates).not.to.be.empty;

        });

        beforeEach(() => {

            sinon.spy(authService, 'promptLogin');
            sinon.spy(authService, 'logout');

        });

        it('should prompt for login', () => {

            $scope.promptLogin();

            expect(authService.promptLogin).to.have.been.calledOnce;

        });

        it('should logout', () => {

            $scope.logout();

            expect(authService.logout).to.have.been.calledOnce;

        });


    });

});