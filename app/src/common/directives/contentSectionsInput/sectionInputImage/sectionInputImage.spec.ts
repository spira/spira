namespace common.directives.contentSectionsInput.sectionInputImage {

    interface TestScope extends ng.IRootScopeService {
        section: any;
        SectionInputImageController: SectionInputImageController;
    }

    describe('Section input image directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: SectionInputImageController,
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

                directiveScope.section = common.models.SectionMock.entity({
                    type: common.models.sections.Image.contentType,
                    content: common.models.sections.ImageMock.entity(),
                });


                compiledElement = $compile(`
                    <section-input-image
                        section="section"
                    ></section-input-image>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).SectionInputImageController;

                let stubbedShow = sinon.stub();
                stubbedShow.onCall(0).returns($q.when(true));
                (<any>directiveController).$mdDialog.show = stubbedShow;

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('section-input-image')).to.be.true;
        });

        it('should be able to add a image section', () => {

            let currentImageCount = directiveController.section.content.images.length;

            directiveController.addImage();

            expect(directiveController.section.content.images).to.have.length(currentImageCount + 1);
        });

        it('should be able to remove an image with prompt', (done) => {

            let currentImageCount = directiveController.section.content.images.length;

            let removePromise = directiveController.removeImage(_.last(directiveController.section.content.images));

            $rootScope.$digest();

            expect(removePromise).eventually.to.be.fulfilled;
            removePromise.then(() => {
                expect(directiveController.section.content.images).to.have.length(currentImageCount - 1);
                done();
            });

        });

        it('should default an image content caption to the images alt tag when image is changed and no caption is set', () => {

            let tabCount = directiveController.addImage();
            let newImageTab = directiveController.section.content.images[tabCount - 1];

            expect(newImageTab.caption).to.be.null;

            newImageTab._image = common.models.ImageMock.entity();
            directiveController.imageChanged(newImageTab);

            expect(newImageTab.caption).to.equal(newImageTab._image.alt);
        });


    });

}