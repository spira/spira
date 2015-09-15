namespace common.services.error {

    let errorService:ErrorService,
        $q:ng.IQService,
        $timeout:ng.ITimeoutService,
        $mdDialog:ng.material.IDialogService,
        $httpBackend:ng.IHttpBackendService,
        $rootScope:ng.IRootScopeService,
        ngRestAdapter:NgRestAdapter.NgRestAdapterService,
        ErrorDialogController:ErrorDialogController;

    describe('Error Service', () => {

        beforeEach(()=> {

            module('app');

            inject((_$rootScope_, $controller, _$httpBackend_, _$q_, _$timeout_, _$mdDialog_, _errorService_, _ngRestAdapter_) => {

                $rootScope = _$rootScope_;
                $httpBackend = _$httpBackend_;
                errorService = _errorService_;
                ngRestAdapter = _ngRestAdapter_;
                $q = _$q_;
                $timeout = _$timeout_;
                $mdDialog = _$mdDialog_;

                ErrorDialogController = $controller(common.services.error.namespace + '.controller', {
                    $mdDialog: $mdDialog,
                    title: "test title",
                    message: "test message",
                    extra: {
                        key: 'value'
                    },
                });

            });

        });

        it('should pop a dialog when requested', () => {

            sinon.spy($mdDialog, 'show');


            errorService.showError("Test title", "test message");

            $timeout.flush();

            expect($mdDialog.show).to.have.been.calledWithMatch({
                locals: {
                    title: "Test title",
                    message: "test message",
                }
            });

            (<Sinon.SinonSpy>$mdDialog.show).restore();

        });

        it('should be able to cancel the error dialog on creation', () => {


            sinon.spy($mdDialog, 'cancel');
            ErrorDialogController.cancelErrorDialog();

            expect($mdDialog.cancel).to.have.been.called;

            (<Sinon.SinonSpy>$mdDialog.cancel).restore();


        });

        it('should trigger an error dialog when an api responds with error', () => {


            sinon.spy(errorService, 'showError');

            $httpBackend.expectGET('/api/a-failing-endpoint').respond(500, {
                message: "Api error message",
            });

            ngRestAdapter.get('/a-failing-endpoint');

            $httpBackend.flush();
            $timeout.flush();

            expect(errorService.showError).to.have.been.calledWithMatch(sinon.match.string, "Api error message", sinon.match.object);

            (<Sinon.SinonSpy>errorService.showError).restore();

        });

        it('should trigger an error dialog with a default message when the api does not provide one', () => {


            sinon.spy(errorService, 'showError');

            $httpBackend.expectGET('/api/a-failing-endpoint').respond(500);

            ngRestAdapter.get('/a-failing-endpoint');

            $httpBackend.flush();
            $timeout.flush();

            expect(errorService.showError).to.have.been.calledWithMatch(sinon.match.string, "No response message", sinon.match.object);

            (<Sinon.SinonSpy>errorService.showError).restore();

        });



    });

}