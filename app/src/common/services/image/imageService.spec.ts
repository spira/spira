namespace common.services.image {

    let seededChance = new Chance(1),
        imageService:common.services.image.ImageService,
        $httpBackend:ng.IHttpBackendService,
        $q:ng.IQService,
        ngRestAdapter:NgRestAdapter.INgRestAdapterService
    ;


    let fixtures = {

        get imageFile() {

            return new function File(){
                this.lastModifiedDate = new Date();
                this.name = seededChance.word() + '.jpg';
            };

        },
        getImage():common.models.Image {

            return new common.models.Image({
                imageId: seededChance.guid(),
                version : Math.floor(chance.date().getTime() / 1000),
                folder : seededChance.word(),
                format : seededChance.pick(['gif', 'jpg', 'png']),
                alt : seededChance.sentence(),
                title : chance.weighted([null, seededChance.sentence()], [1, 2]),
            });

        },
        getImages() {

            return chance.unique(fixtures.getImage, 30);
        }

    };

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

        afterEach(() => {
            $httpBackend.verifyNoOutstandingExpectation();
            $httpBackend.verifyNoOutstandingRequest();
        });

        describe('Image upload', () => {

            it('should be able to upload an image to cloudinary', () => {

                let fixedImageId = chance.guid(),
                    ts = moment().unix(),
                    image = fixtures.imageFile;

                sinon.stub((<any>imageService).ngRestAdapter, 'uuid').returns(fixedImageId); //fix the value of the ngRestAdapter.uuid() method

                let uploadPromise = imageService.uploadImage({
                    file: image,
                    alt: 'image test',
                });


                $httpBackend.expectGET('/api/cloudinary/signature?public_id='+fixedImageId+'&timestamp='+ts+'&type=upload').respond({
                    signature: 'this-is-the-signature',
                    apiKey: 'abc-123'
                });

                $httpBackend.expectPOST('https://api.cloudinary.com/v1_1/spira/image/upload', (req) => {
                    //console.log('req', req);
                    return true; //@todo set expectation
                }).respond({
                    bytes: 517112,
                    created_at: "2015-09-07T06:24:16Z",
                    etag: "67131e8d70ecb06a8400554f5eef8c77",
                    format: "jpg",
                    height: 1632,
                    original_filename: image.name,
                    public_id: fixedImageId,
                    resource_type: "image",
                    secure_url: `https://res.cloudinary.com/spira/image/upload/v${ts}/${fixedImageId}.jpg`,
                    signature: "3c2255855ac023ff1bde0faa454667e9494152a6",
                    tags: [],
                    type: "upload",
                    url: `http://res.cloudinary.com/spira/image/upload/v${ts}/${fixedImageId}.jpg`,
                    version: ts,
                    width: 2448,
                });

                $httpBackend.expectPUT('/api/images/'+fixedImageId, {
                    imageId: fixedImageId,
                    version: ts,
                    alt: "image test",
                    title: "image test",
                    format:'jpg',
                }).respond(204);

                $httpBackend.flush(3);


                expect(uploadPromise).eventually.to.be.fulfilled;
                expect(uploadPromise).eventually.to.be.instanceOf(common.models.Image);


            });

        });

        describe('Image retrieval', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let images = _.clone(fixtures.getImages()); //get a set of images

            it('should return the first set of images from the paginator', () => {

                $httpBackend.expectGET('/api/images').respond(_.take(images, 10));

                let imagePaginator = imageService.getImagesPaginator();

                let firstSet = imagePaginator.getNext(10);

                expect(firstSet).eventually.to.be.fulfilled;
                expect(firstSet).eventually.to.deep.equal(_.take(images, 10));

                $httpBackend.flush();

            });


        });

    });

}