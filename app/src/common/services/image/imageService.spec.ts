namespace common.services.image {

    let seededChance = new Chance(1),
        imageService:common.services.image.ImageService,
        $httpBackend:ng.IHttpBackendService,
        $q:ng.IQService,
        ngRestAdapter:NgRestAdapter.INgRestAdapterService,
        $rootScope:ng.IRootScopeService,
        $timeout: ng.ITimeoutService
    ;


    let fixtures = {

        get imageFile() {

            return new function File(){
                this.lastModifiedDate = new Date();
                this.name = seededChance.word() + '.jpg';
            };

        }

    };

    describe('Image Service', () => {

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _imageService_, _$q_, _ngRestAdapter_, _$rootScope_, _$timeout_) => {

                if (!imageService) { // Don't rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    imageService = _imageService_;
                    $q = _$q_;
                    ngRestAdapter = _ngRestAdapter_;
                    $rootScope = _$rootScope_;
                    $timeout = _$timeout_;
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


                $httpBackend.expectGET(new RegExp('\/api\/cloudinary\/signature\\?public_id='+fixedImageId+'&timestamp=[\\d]+&type=upload')).respond({
                    signature: 'this-is-the-signature',
                    apiKey: 'abc-123'
                });

                $httpBackend.expectPOST('https://api.cloudinary.com/v1_1/spira/image/upload').respond({ //as the request is a FormObject, for some reason httpbackend won't passthrough the request body so assertions can't be added
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

                (<any>imageService).ngRestAdapter.uuid.restore();

            });


            it('should notify progress on upload', () => {

                let fixedImageId = chance.guid(),
                    ts = moment().unix(),
                    image = fixtures.imageFile;

                $httpBackend.expectGET(new RegExp('\/api\/cloudinary\/signature\\?public_id='+fixedImageId+'&timestamp=[\\d]+&type=upload')).respond({
                    signature: 'this-is-the-signature',
                    apiKey: 'abc-123'
                });


                sinon.stub((<any>imageService).ngRestAdapter, 'uuid').returns(fixedImageId); //fix the value of the ngRestAdapter.uuid() method

                let mockUploadDeferred = $q.defer();
                //mock the progress method
                (<any>mockUploadDeferred.promise).progress = (callback) => {
                    return mockUploadDeferred.promise.then(null, null, callback);
                };

                sinon.stub((<any>imageService).ngFileUpload, 'upload').returns(mockUploadDeferred.promise);

                let progressSpy = sinon.spy();
                let uploadPromise = imageService.uploadImage({
                    file: image,
                    alt: 'image test',
                }).then(null, null, progressSpy);


                $timeout.flush();

                $httpBackend.flush(1);


                mockUploadDeferred.notify({
                    loaded: 5,
                    total: 10,
                });

                mockUploadDeferred.notify({
                    loaded: 8,
                    total: 10,
                });

                $httpBackend.expectPUT('/api/images/'+fixedImageId, {
                    imageId: fixedImageId,
                    version: ts,
                    alt: "image test",
                    title: "image test",
                    format:'jpg',
                }).respond(204);

                mockUploadDeferred.resolve({
                    data: {
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
                    }
                }); //call the progress callback function


                $httpBackend.flush(1);

                $rootScope.$apply();

                uploadPromise.then(() => {
                    progressSpy.should.have.been.calledWith(sinon.match({ event: 'cloudinary_signature' }));
                    progressSpy.should.have.been.calledWith(sinon.match({ event: 'cloudinary_upload', progressValue: 50 }));
                    progressSpy.should.have.been.calledWith(sinon.match({ event: 'cloudinary_upload', progressValue: 80 }));
                    progressSpy.should.have.been.calledWith(sinon.match({ event: 'api_link' }));
                    progressSpy.should.have.callCount(4);
                });

                expect(uploadPromise).eventually.to.be.fulfilled;
                expect(uploadPromise).eventually.to.be.instanceOf(common.models.Image);


                (<any>imageService).ngRestAdapter.uuid.restore();
                (<any>imageService).ngFileUpload.upload.restore();

            });

        });

        describe('Image retrieval', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let images = _.clone(common.models.ImageMock.collection(10)); //get a set of images

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