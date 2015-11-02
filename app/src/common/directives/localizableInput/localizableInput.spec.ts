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
            mockUpdatedLocalizations:common.models.Localization<common.models.Article>[] = common.models.LocalizationMock.collection()
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
                stubbedShow.returns($q.when(mockUpdatedLocalizations));
                (<any>directiveController).$mdDialog.show = stubbedShow;
            }

        });

        it('should initialise the directive on an input element', () => {

            expect($(compiledElement).hasClass('ng-untouched')).to.be.true;
        });

        it('should prompt a dialog to add a localization, which returns updated localization entities', () => {

            directiveController.promptAddLocalization(global.MouseEventMock.getMock());


            expect((<any>directiveController).$mdDialog.show).to.have.been.calledWith(sinon.match({
                locals: {
                    localizations: directiveScope.article._localizations,
                    attributeKey: 'title',
                    inputNodeName: 'input',
                    originalValue: 'Test Title',
                }
            }));

            directiveScope.$apply();

            expect(directiveScope.article._localizations).to.deep.equal(mockUpdatedLocalizations);
            expect($(compiledElement).hasClass('ng-dirty')).to.be.true;

        });




    });

}