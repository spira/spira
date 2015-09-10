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
                imagesPaginator = imageService.getImagesPaginator().setCount(12);
                imagesPaginator.entityCountTotal = 50;
                images = common.models.ImageMock.collection(12);

                MediaController = $controller(app.admin.media.namespace + '.controller', {
                    perPage: 12,
                    imageService: imageService,
                    imagesPaginator: imagesPaginator,
                    initialImages: images,
                    $stateParams: $stateParams
                });
            });

        });

        describe('Initialisation', () => {

            it('should have a set of images loaded', () => {

                expect(MediaController.images[0]).to.be.instanceOf(common.models.Image);
            });

        });

        describe('Image upload', () => {


            it('should be able to upload an image', () => {

                let imageViewCount = MediaController.images.length;
                let mockUploadDeferred = $q.defer();

                sinon.stub((<any>MediaController).imageService, 'uploadImage').returns(mockUploadDeferred.promise); //mock the image service

                let image = {
                    file: new function File(){
                        this.lastModifiedDate = new Date();
                        this.name = 'upload.jpg';
                    },
                    alt: "Image alt test",
                    title: "Image title"
                };

                MediaController.uploadImage(image);

                mockUploadDeferred.notify({
                    event: 'cloudinary_signature'
                });
                mockUploadDeferred.notify({
                    event: 'cloudinary_upload'
                });
                mockUploadDeferred.notify({
                    event: 'api_link'
                });

                mockUploadDeferred.resolve(common.models.ImageMock.entity({title:image.title, alt:image.alt}));

                $scope.$apply();


                expect(MediaController.images.length).to.equal(imageViewCount); //length should not have changed
                expect(MediaController.images[0].title).to.equal(image.title); //first image should have been pushed on


            });

        });

    });

}