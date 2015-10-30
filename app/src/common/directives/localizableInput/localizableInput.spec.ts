namespace common.directives.localizableInput {

    interface TestScope extends ng.IRootScopeService {
        article:common.models.Article;
        LocalizableInputController: LocalizableInputController;
    }

    describe('Localizable input directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: LocalizableInputController,
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

                directiveScope.article = common.models.ArticleMock.entity({
                    title: 'Test Title'
                });

                compiledElement = $compile(`
                    <input ng-model="article.title" localizable-input="article._localizations">
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).LocalizableInputController;

                let stubbedShow = sinon.stub();
                stubbedShow.returns($q.when(mockImage));
                (<any>directiveController).$mdDialog.show = stubbedShow;
            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('ng-untouched')).to.be.true;
        });




    });

}