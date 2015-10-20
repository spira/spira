namespace common.directives.selectMediaImage.dialog {

    describe('Select media image dialog controller', () => {

        let images:common.models.Image[] = common.models.ImageMock.collection(12),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            imageService:common.services.image.ImageService,
            SelectMediaImageDialogController:SelectMediaImageDialogController,
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

                SelectMediaImageDialogController = $controller(common.directives.selectMediaImage.dialog.namespace + '.controller', {
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


            expect(SelectMediaImageDialogController.library).to.have.length(12);
            expect(SelectMediaImageDialogController.library[0]).to.be.instanceOf(common.models.Image);

        });

        it('should be able to toggle selection of an image', () => {

            SelectMediaImageDialogController.selectedImage = null;

            SelectMediaImageDialogController.toggleImageSelection(SelectMediaImageDialogController.library[2]);

            expect(SelectMediaImageDialogController.selectedImage).to.deep.equal(SelectMediaImageDialogController.library[2]);

            SelectMediaImageDialogController.toggleImageSelection(SelectMediaImageDialogController.library[2]);

            expect(SelectMediaImageDialogController.selectedImage).to.be.null;

        });

        it('should be able to resolve a selected image', () => {

            SelectMediaImageDialogController.toggleImageSelection(SelectMediaImageDialogController.library[0]);

            SelectMediaImageDialogController.selectImage();

            expect((<any>SelectMediaImageDialogController).$mdDialog.hide).to.have.been.calledWith(SelectMediaImageDialogController.selectedImage);

        });

        it('should cancel the dialog when no image is selected', () => {

            SelectMediaImageDialogController.selectedImage = null;

            SelectMediaImageDialogController.selectImage();

            expect((<any>SelectMediaImageDialogController).$mdDialog.cancel).to.have.been.called;

        });

        it('should be able to browse through multiple pages of images', () => {

            let pageChangePromise = SelectMediaImageDialogController.goToPage(2);

            expect(SelectMediaImageDialogController.currentPage).to.equal(2);

            $rootScope.$apply();

            expect(pageChangePromise).eventually.to.deep.equal(images);

        });

        it('should be able to cancel the dialog', () => {

            SelectMediaImageDialogController.cancelDialog();

            expect((<any>SelectMediaImageDialogController).$mdDialog.cancel).to.have.been.called;

        });



    });

}