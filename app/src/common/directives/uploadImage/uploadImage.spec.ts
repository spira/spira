namespace common.directives.uploadImage {

    interface TestScope extends ng.IRootScopeService {
        testImage: common.models.Image;
        UploadImageController: UploadImageController;
    }

    describe('Upload image directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: UploadImageController,
            $q:ng.IQService
        ;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_, _$q_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
                $q = _$q_;
            });

            //only initialise the directive once to speed up the testing
            if (!directiveController){

                directiveScope = <TestScope>$rootScope.$new();

                compiledElement = $compile(`
                    <upload-image
                        ng-model="testImage">
                    </upload-image>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).UploadImageController;


                directiveController.imageUploadForm = global.FormControllerMock.getMock();

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('ng-untouched')).to.be.true;
        });

        it('should be able to upload an image', () => {

            let mockUploadDeferred = $q.defer();

            sinon.stub((<any>directiveController).imageService, 'uploadImage').returns(mockUploadDeferred.promise); //mock the image service

            let image = {
                file: new function File(){
                    this.lastModifiedDate = new Date();
                    this.name = 'upload.jpg';
                },
                alt: "Image alt test",
                title: "Image title"
            };

            directiveController.uploadImage(image);

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

            $rootScope.$apply();

            expect(directiveController.currentImage).to.be.instanceOf(common.models.Image); //length should not have changed
            expect(directiveController.currentImage.title).to.equal(image.title); //first image should have been pushed on
            expect(directiveController.imageUploadForm.$setPristine).to.have.been.called;

        });




    });

}