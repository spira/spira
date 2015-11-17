namespace common.directives.selectMediaImage {

    interface TestScope extends ng.IRootScopeService {
        testImage: common.models.Image;
        SelectMediaImageController: SelectMediaImageController;
    }

    describe('Select media image directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: SelectMediaImageController,
            $q:ng.IQService,
            mockImage:common.models.Image = common.models.ImageMock.entity()
        ;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_, _$q_, _$mdDialog_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
                $q = _$q_;

            });

            //only initialise the directive once to speed up the testing
            if (!directiveController){

                directiveScope = <TestScope>$rootScope.$new();

                directiveScope.testImage = null;

                compiledElement = $compile(`
                    <select-media-image
                        ng-model="testImage">
                    </select-media-image>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).SelectMediaImageController;

                let stubbedShow = sinon.stub();
                stubbedShow.returns($q.when(mockImage));
                (<any>directiveController).$mdDialog.show = stubbedShow;
            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('ng-untouched')).to.be.true;
        });

        it('should prompt a dialog that resolves a new image when confirmed', () => {

            expect(directiveScope.testImage).to.be.null;

            directiveController.promptSelectImageDialog('upload');

            directiveScope.$apply();

            expect((<any>directiveController).$mdDialog.show).to.have.been.called;
            expect(directiveController.currentImage).to.be.instanceOf(common.models.Image);
            expect(directiveScope.testImage).to.be.instanceOf(common.models.Image);

        });




    });

}