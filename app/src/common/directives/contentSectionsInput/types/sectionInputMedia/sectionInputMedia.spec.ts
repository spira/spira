namespace common.directives.contentSectionsInput.sectionInputMedia {

    interface TestScope extends ng.IRootScopeService {
        section: any;
        SectionInputMediaController: SectionInputMediaController;
    }

    describe('Section input image directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: SectionInputMediaController,
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
                    type: common.models.sections.Media.contentType,
                    content: common.models.sections.MediaMock.entity(),
                });


                compiledElement = $compile(`
                    <section-input-media
                        section="section"
                    ></section-input-media>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).SectionInputMediaController;

                let stubbedShow = sinon.stub();
                stubbedShow.onCall(0).returns($q.when(true));
                (<any>directiveController).$mdDialog.show = stubbedShow;

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('section-input-media')).to.be.true;
        });

        it('should be able to add a media section', () => {

            let currentImageCount = directiveController.section.content.media.length;

            directiveController.addMedia();

            expect(directiveController.section.content.media).to.have.length(currentImageCount + 1);
        });

        it('should be able to remove a media section with prompt', (done) => {

            let currentImageCount = directiveController.section.content.media.length;

            let removePromise = directiveController.removeMedia(_.last(directiveController.section.content.media));

            $rootScope.$digest();

            expect(removePromise).eventually.to.be.fulfilled;
            removePromise.then(() => {
                expect(directiveController.section.content.media).to.have.length(currentImageCount - 1);
                done();
            });

        });

        it('should default an image content caption to the images alt tag when image is changed and no caption is set', () => {

            let tabCount = directiveController.addMedia();
            let newImageTab = <common.models.sections.IImageContent>directiveController.section.content.media[tabCount - 1];

            expect(newImageTab.caption).to.be.null;

            newImageTab._image = common.models.ImageMock.entity();
            directiveController.imageChanged(newImageTab);

            expect(newImageTab.caption).to.equal(newImageTab._image.alt);
        });


    });

}