namespace common.directives.localizableInput.dialog {

    describe.skip('Localizable input dialog controller', () => {

        let images:common.models.Image[] = common.models.ImageMock.collection(12),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            imageService:common.services.image.ImageService,
            LocalizableInputDialogController:LocalizableInputDialogController,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _imageService_, _$q_) => {
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
                    $mdDialog: {
                        cancel: sinon.stub(),
                        hide: sinon.stub()
                    },
                    imageService: imageService
                });


                $rootScope.$apply();

            });

        });

        it('should be able to resolve image paginator with initial images', () => {


            expect(LocalizableInputDialogController.library).to.have.length(12);
            expect(LocalizableInputDialogController.library[0]).to.be.instanceOf(common.models.Image);

        });

        it('should be able to toggle selection of an image', () => {

            LocalizableInputDialogController.selectedImage = null;

            LocalizableInputDialogController.toggleImageSelection(LocalizableInputDialogController.library[2]);

            expect(LocalizableInputDialogController.selectedImage).to.deep.equal(LocalizableInputDialogController.library[2]);

            LocalizableInputDialogController.toggleImageSelection(LocalizableInputDialogController.library[2]);

            expect(LocalizableInputDialogController.selectedImage).to.be.null;

        });

        it('should be able to resolve a selected image', () => {

            LocalizableInputDialogController.toggleImageSelection(LocalizableInputDialogController.library[0]);

            LocalizableInputDialogController.selectImage();

            expect((<any>LocalizableInputDialogController).$mdDialog.hide).to.have.been.calledWith(LocalizableInputDialogController.selectedImage);

        });

        it('should cancel the dialog when no image is selected', () => {

            LocalizableInputDialogController.selectedImage = null;

            LocalizableInputDialogController.selectImage();

            expect((<any>LocalizableInputDialogController).$mdDialog.cancel).to.have.been.called;

        });

        it('should be able to browse through multiple pages of images', () => {

            let pageChangePromise = LocalizableInputDialogController.goToPage(2);

            expect(LocalizableInputDialogController.currentPage).to.equal(2);

            $rootScope.$apply();

            expect(pageChangePromise).eventually.to.deep.equal(images);

        });

        it('should be able to cancel the dialog', () => {

            LocalizableInputDialogController.cancelDialog();

            expect((<any>LocalizableInputDialogController).$mdDialog.cancel).to.have.been.called;

        });



    });

}