namespace common.services.image {

    let seededChance = new Chance(1),
        imageService:common.services.image.ImageService,
        $httpBackend:ng.IHttpBackendService,
        $q:ng.IQService,
        ngRestAdapter:NgRestAdapter.INgRestAdapterService
    ;

    describe('Image Service', () => {

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _imageService_, _$q_, _ngRestAdapter_) => {

                if (!imageService) { // Don't rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    imageService = _imageService_;
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