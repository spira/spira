

describe('Registration', () => {

    describe('Configuration', () => {

        let RegistrationController:ng.IControllerService,
            $scope:app.partials.registration.IScope,
            $mdDialog:ng.material.IDialogService,
            userService:common.services.UserService,
            $state:ng.ui.IStateService
        ;

        beforeEach(() => {
            module('app');
        });

        beforeEach(()=> {
            inject(($controller, $rootScope, _userService_, _$state_) => {
                $scope = $rootScope.$new();
                userService = _userService_;
                $state = _$state_;
                RegistrationController = $controller(app.partials.registration.namespace+'.controller', {
                    $scope: $scope,
                    userService : userService,
                    $state: $state
                });

            })
        });

        it('should be a valid controller', () => {

            expect(RegistrationController).to.be.ok;
        });

        beforeEach(() => {

            sinon.spy(userService, 'registerAndLogin');
            sinon.spy($state, 'go');

        });

        it('should attempt a registration of a user', () => {

            let email = 'email@example.com', password = 'hunter2', first = 'Joe', last = 'Bloggs';

            $scope.registerUser(email, password, first, last);

            expect(userService.registerAndLogin).to.have.been.calledWithExactly(email, password, first, last);
            expect($state.go).not.to.have.been.called;

        });


    });

});