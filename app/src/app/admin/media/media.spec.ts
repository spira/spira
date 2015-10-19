namespace app.admin.media {

    describe('Admin - Media', () => {

        let images:common.models.Image[],
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IMediaStateParams = {
                page:1
            },
            imageService:common.services.image.ImageService,
            imagesPaginator:common.services.pagination.Paginator,
            MediaController:MediaController,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _imageService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $q = _$q_;
                imageService = _imageService_;

                // Setup imagesPaginator before injection
                imagesPaginator = imageService.getPaginator().setCount(12);
                imagesPaginator.entityCountTotal = 50;
                images = common.models.ImageMock.collection(12);

                MediaController = $controller(app.admin.media.namespace + '.controller', {
                    perPage: 12,
                    imageService: imageService,
                    imagesPaginator: imagesPaginator,
                    initialImages: images,
                    $stateParams: $stateParams
                });

                MediaController.imageUploadForm = global.FormControllerMock.getMock();

            });

        });

        describe('Initialisation', () => {

            it('should be able to resolve image paginator with initial images', () => {

                let pageCount = (<any>MediaConfig.state.resolve).perPage();
                let imagesPaginator = (<any>MediaConfig.state.resolve).imagesPaginator(imageService, pageCount);

                sinon.stub(imagesPaginator, 'getPage').returns('mockresponse');
                let mockStateParams = {
                    page: 1,
                };
                let initialImages = (<any>MediaConfig.state.resolve).initialImages(imagesPaginator, mockStateParams);

                expect(initialImages).to.equal('mockresponse');

            });

            it('should have a set of images loaded', () => {

                expect(MediaController.images[0]).to.be.instanceOf(common.models.Image);
            });

            it('should push in a new image to the library when one is uploaded', () => {

                let imageCount = MediaController.images.length;
                let newImage = common.models.ImageMock.entity();
                MediaController.imageUploaded(newImage);

                expect(MediaController.images[0].imageId).to.equal(newImage.imageId);
                expect(MediaController.images).to.have.length(imageCount);
            });

        });

    });

}