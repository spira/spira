///<reference path="../../src/global.d.ts" />

let expect:Chai.ExpectStatic = chai.expect;

describe('Bootstrap', () => {

    describe('isCurrentUrl', () => {

        let AppCtrl, ngJwtAuthService, $state;

        beforeEach(() => {

            module('app');
        });

        beforeEach(()=> {
            inject(($controller, _$mdSidenav_, _ngJwtAuthService_, _$state_) => {

                ngJwtAuthService = _ngJwtAuthService_;
                $state = _$state_;

                AppCtrl = $controller('app.controller', {
                    $mdSidenav : _$mdSidenav_,
                    ngJwtAuthService : ngJwtAuthService,
                    $state : $state
                });
            })
        });

        it('should pass a dummy test', () => {

            expect(AppCtrl).to.be.ok;
        });


        describe('Login actions', () => {

            beforeEach(() => {

                sinon.spy(ngJwtAuthService, 'promptLogin');
                sinon.spy(ngJwtAuthService, 'logout');

            });

            it('should prompt for login', () => {

                AppCtrl.promptLogin();

                expect(ngJwtAuthService.promptLogin).to.have.been.calledOnce;

            });

            it('should logout', () => {

                AppCtrl.logout();

                expect(ngJwtAuthService.logout).to.have.been.calledOnce;

            });

        });

        describe('navigation actions', () => {

            it('should navigate to the user profile', () => {

                sinon.spy($state, 'go');

                AppCtrl.goToUserProfile();

                expect($state.go).to.have.been.calledWith('app.user.profile');

                (<any>$state).go.restore();

            });

        });


    });

});