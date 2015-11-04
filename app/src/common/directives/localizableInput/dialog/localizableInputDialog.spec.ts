namespace common.directives.localizableInput.dialog {

    describe('Localizable input dialog controller', () => {

        let images:common.models.Image[] = common.models.ImageMock.collection(12),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            notificationService:common.services.notification.NotificationService,
            LocalizableInputDialogController:LocalizableInputDialogController,
            $q:ng.IQService,
            mockInitLocalizations:common.models.Localization<common.models.Article>[] = [
                common.models.LocalizationMock.entity({
                    localizations: {
                        title: "This is a title",
                    },
                    regionCode: 'uk',
                }),
                common.models.LocalizationMock.entity({
                    localizations: {
                        body: "This is the body",
                    },
                    regionCode: 'au',
                }),
                common.models.LocalizationMock.entity({
                    localizations: {
                        title: "Kiwi Title",
                    },
                    regionCode: 'nz',
                })
            ],
            toastMock:common.services.notification.Toast;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _$q_, _ngRestAdapter_, _notificationService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                notificationService = _notificationService_;
                $q = _$q_;

                toastMock = notificationService.toast('test');

                LocalizableInputDialogController = $controller(common.directives.localizableInput.dialog.namespace + '.controller', {
                    localizations: mockInitLocalizations,
                    attributeKey: 'title',
                    inputNodeName: 'input',
                    originalValue: 'Original Title',
                    regionService: {
                        supportedRegions: [
                            {
                                code: 'au',
                                name: 'Australia',
                            },
                            {
                                code: 'uk',
                                name: 'United Kingdom',
                            },
                            {
                                code: 'nz',
                                name: 'New Zealand',
                            },
                            {
                                code: 'us',
                                name: 'USA',
                            },
                            {
                                code: 'fr',
                                name: 'France',
                            },
                        ]
                    },
                    $mdDialog: {
                        cancel: sinon.stub(),
                        hide: sinon.stub()
                    },
                    notificationService: notificationService,
                    ngRestAdapter: _ngRestAdapter_
                });

                $rootScope.$apply();

            });

        });

        it('should initialise a localizations map from source localizations', () => {

            expect(LocalizableInputDialogController.localizationMap).to.have.property('uk', "This is a title");
            expect(LocalizableInputDialogController.localizationMap).to.have.property('au', undefined);
            expect(LocalizableInputDialogController.localizationMap).to.have.property('nz', "Kiwi Title");
            expect(LocalizableInputDialogController.localizationMap).to.have.property('us', undefined);
            expect(LocalizableInputDialogController.localizationMap).to.have.property('fr', undefined);
        });

        it('should be able to copy the original value to a localization', () => {

            toastMock.pop = sinon.stub().returns($q.when(null));
            (<any>LocalizableInputDialogController).notificationService.toast = sinon.stub().returns(toastMock);

            LocalizableInputDialogController.copyFromOriginal('au');

            $rootScope.$apply();
            expect(LocalizableInputDialogController.localizationMap['au']).to.equal(LocalizableInputDialogController.originalValue);
        });

        it('should be able to copy the original value to a localization, then undo when user changes their mind', () => {

            toastMock.pop = sinon.stub().returns($q.when('Undo'));
            (<any>LocalizableInputDialogController).notificationService.toast = sinon.stub().returns(toastMock);

            let originalValue = LocalizableInputDialogController.localizationMap['au'];
            LocalizableInputDialogController.copyFromOriginal('au');

            expect(LocalizableInputDialogController.localizationMap['au']).to.equal(LocalizableInputDialogController.originalValue);
            $rootScope.$apply();
            expect((<any>LocalizableInputDialogController).notificationService.toast).to.have.been.calledWith('Copy Undone');
            expect(LocalizableInputDialogController.localizationMap['au']).to.equal(originalValue);
        });

        it('should be able to resolve the updated localizations', () => {

            LocalizableInputDialogController.localizationMap['au'] = "Aussie title";
            LocalizableInputDialogController.localizationMap['nz'] = "";
            LocalizableInputDialogController.localizationMap['us'] = "Murican title";

            LocalizableInputDialogController.saveLocalizations();

            expect((<any>LocalizableInputDialogController).$mdDialog.hide).to.have.been.calledWith([
                {
                    localizableId: sinon.match.string,
                    localizableType: null,
                    localizations: {
                        body: "This is the body",
                        title: "Aussie title"
                    },
                    regionCode: 'au'
                },
                {
                    localizableId: sinon.match.string,
                    localizableType: null,
                    localizations: {
                        title: "This is a title"
                    },
                    regionCode: 'uk'
                },
                {
                    localizableId: sinon.match.string,
                    localizableType: null,
                    localizations: {
                        title: undefined
                    },
                    regionCode: 'nz'
                },
                {
                    localizableId: null,
                    localizableType: null,
                    localizations: {
                        title: "Murican title"
                    },
                    regionCode: 'us'
                }
            ]);
        });


        it('should be able to cancel the dialog', () => {

            LocalizableInputDialogController.cancelDialog();

            expect((<any>LocalizableInputDialogController).$mdDialog.cancel).to.have.been.called;

        });

    });

}