(() => {

    describe('Notification Service', () => {

        let notificationService:common.services.notification.NotificationService;
        let $mdToast:ng.material.IToastService;
        let $rootScope:global.IRootScope;

        beforeEach(()=> {

            module('app');

            inject((_notificationService_, _$mdToast_, _$rootScope_) => {

                if (!notificationService) { // Don't rebind, so each test gets the singleton
                    notificationService = _notificationService_;
                    $mdToast = _$mdToast_;
                    $rootScope = _$rootScope_;

                    sinon.spy($mdToast, 'show');
                }

            });

        });

        describe('Initialisation', () => {

            it('should be an injectable service', () => {

                return expect(notificationService).to.be.an('object');
            });

        });

        describe('Toast', () => {

            it('should be able to override settings', () => {

                notificationService.toast('foobar').options({template:'<md-toast>New Message</md-toast>'}).pop();

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/New Message/)));

            });

            it('should be able to show a position fixed toast', () => {

                notificationService.toast('foobar').pop();

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/foobar/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/<md-toast class="md-toast-fixed">/)));

            });

            it('should be able to show a normal toast on parent element', () => {

                notificationService.toast('foobar').options({parent:'#parent'}).pop();

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/foobar/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/<md-toast>/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("parent", sinon.match(/#parent/)));

            });

        });

    });

})();