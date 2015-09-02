namespace common.services.imageUpload {

    let seededChance = new Chance(1),
        imageUploadService:common.services.imageUpload.ImageService,
        $httpBackend:ng.IHttpBackendService,
        ngRestAdapter:NgRestAdapter.INgRestAdapterService
    ;

    describe('Image Upload Service', () => {

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _imageUploadService_, _$q_, _ngRestAdapter_) => {

                if (!imageUploadService) { // Don't rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    imageUploadService = _imageUploadService_;
                    $q = _$q_;
                    ngRestAdapter = _ngRestAdapter_;
                }

            });

        });

        it('should be able to upload an image to cloudinary', () => {

            expect(true).to.be.true; // no kidding @todo

        });

    });

}