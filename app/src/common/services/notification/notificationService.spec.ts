(() => {

    describe('Notification Service', () => {

        let notificationService:common.services.notification.NotificationService;
        let $mdToast:ng.material.IToastService;

        beforeEach(()=> {

            module('app');

            inject((_notificationService_, _$mdToast_) => {

                if (!notificationService) { // Don't rebind, so each test gets the singleton
                    notificationService = _notificationService_;
                    $mdToast = _$mdToast_;
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

            it('should be able to show a position fixed toast', () => {

                notificationService.showToast('foobar');

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/foobar/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/<md-toast class="md-toast-fixed">/)));

            });

            it('should be able to show a normal toast on parent element', () => {

                notificationService.showToast('foobar', '#parent');

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/foobar/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/<md-toast>/)));
                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("parent", sinon.match(/#parent/)));

            });

        });

    });

})();