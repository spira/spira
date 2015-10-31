namespace common.directives.localizableInput.dialog {

    describe.only('Localizable input dialog controller', () => {

        let images:common.models.Image[] = common.models.ImageMock.collection(12),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            imageService:common.services.image.ImageService,
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
            ];

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _imageService_, _$q_, _ngRestAdapter_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                imageService = _imageService_;
                $q = _$q_;

                let imagePaginatorMock:common.services.pagination.Paginator = imageService.getPaginator();
                imagePaginatorMock.setCount = sinon.stub().returns(imagePaginatorMock);
                imagePaginatorMock.getPages = sinon.stub().returns(3);
                imagePaginatorMock.getPage = sinon.stub().returns($q.when(images));
                imageService.getPaginator = sinon.stub().returns(imagePaginatorMock);

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
                    notificationService: null,
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

        });

        it('should be able to resolve the updated localizations', () => {

            LocalizableInputDialogController.localizationMap['au'] = "Aussie title";
            LocalizableInputDialogController.localizationMap['nz'] = "";
            LocalizableInputDialogController.localizationMap['us'] = "Murican title";

            LocalizableInputDialogController.saveLocalizations();

            expect((<any>LocalizableInputDialogController).$mdDialog.hide).to.have.been.calledWith([
                {
                    localizableId: sinon.match.string,
                    localizableType: sinon.match.falsy,
                    localizations: {
                        body: "This is the body",
                        title: "Aussie title"
                    },
                    regionCode: 'au'
                },
                {
                    localizableId: sinon.match.string,
                    localizableType: sinon.match.falsy,
                    localizations: {
                        title: "This is a title"
                    },
                    regionCode: 'uk'
                },
                {
                    localizableId: sinon.match.string,
                    localizableType: sinon.match.falsy,
                    localizations: {
                    },
                    regionCode: 'nz'
                },
                {
                    localizableId: sinon.match.string,
                    localizableType: sinon.match.falsy,
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