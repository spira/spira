

describe('Registration', () => {

    describe('Configuration', () => {

        let RegistrationController:ng.IControllerService,
            $scope:app.partials.registration.IScope,
            $mdDialog:ng.material.IDialogService,
            userService:common.services.user.UserService,
            $q:ng.IQService,
            userServiceMock:common.services.user.UserService = <common.services.user.UserService>{
                registerAndLogin: (email, password, first, last) => $q.when(true),
            },
            $state:ng.ui.IStateService
        ;

        beforeEach(() => {
            module('app');
        });

        beforeEach(()=> {
            inject(($controller, $rootScope, _userService_, _$state_, _$q_) => {
                $scope = $rootScope.$new();
                $q = _$q_;
                userService = _userService_;
                $state = _$state_;
                RegistrationController = $controller(app.partials.registration.namespace+'.controller', {
                    $scope: $scope,
                    userService : userServiceMock,
                    $state: $state
                });

            })
        });

        it('should be a valid controller', () => {

            expect(RegistrationController).to.be.ok;
        });

        beforeEach(() => {

            sinon.spy(userServiceMock, 'registerAndLogin');
            sinon.spy($state, 'go');

        });

        afterEach(() => {
            (<any>userServiceMock.registerAndLogin).restore();
            (<any>$state.go).restore();
        });

        let email = 'email@example.com', password = 'hunter2', first = 'Joe', last = 'Bloggs';

        it('should attempt registration of a user', () => {

            $scope.registerUser(email, password, first, last);

            expect(userServiceMock.registerAndLogin).to.have.been.calledWithExactly(email, password, first, last);
            expect($state.go).not.to.have.been.called;

        });

        it('should attempt registration of a user and redirect to the profile page when requested', () => {

            let userPromise = $scope.registerUser(email, password, first, last, true);

            expect(userServiceMock.registerAndLogin).to.have.been.calledWithExactly(email, password, first, last);

            expect(userPromise).eventually.to.be.fulfilled;

            $scope.$apply();

            return userPromise.finally(() => {
                expect($state.go).to.have.been.called;
            });

        });


    });

});