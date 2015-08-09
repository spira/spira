///<reference path="../../src/global.d.ts" />

let expect:Chai.ExpectStatic = chai.expect;

describe('Bootstrap', () => {

    describe('isCurrentUrl', () => {

        let AppCtrl, ngJwtAuthService;

        beforeEach(() => {

            module('app');
        });

        beforeEach(()=> {
            inject(($controller, _$mdSidenav_, _ngJwtAuthService_, _$state_) => {

                ngJwtAuthService = _ngJwtAuthService_;

                AppCtrl = $controller('app.controller', {
                    $mdSidenav : _$mdSidenav_,
                    ngJwtAuthService : ngJwtAuthService,
                    $state : _$state_
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



    });

});