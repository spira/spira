namespace common.directives.entitySearch {

    interface TestScope extends ng.IRootScopeService {
        testNgModel: common.models.Article;
        EntitySearchController: EntitySearchController;
    }

    describe('Entity search directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement:ng.IAugmentedJQuery,
            directiveController:common.directives.entitySearch.EntitySearchController,
            article:common.models.Article = common.models.ArticleMock.entity(),
            $q:ng.IQService;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_, _$q_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
                $q = _$q_;
            });

            // Only initialise the directive once to speed up the testing
            if (!directiveController){

                directiveScope = <TestScope>$rootScope.$new();

                directiveScope.testNgModel = article;

                compiledElement = $compile(`
                    <entity-search
                        ng-model="testNgModel"
                        model-type="article"
                        thumbnail="true"
                        field="title"
                        >
                    </entity-search>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).EntitySearchController;

                let stubQuery = sinon.stub();
                stubQuery.withArgs({'title' : ['exists']}).returns($q.when([common.models.ArticleMock.entity()]))
                stubQuery.withArgs({'title' : ['not-exists']}).returns($q.reject(true));

                (<any>directiveController).entitiesPaginator.complexQuery = stubQuery;

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('entity-search')).to.be.true;

            expect(directiveController.selectedEntities[0]).to.deep.equal(article);

        });

        it('should be able to auto-complete search for entities', () => {

            let resultsPromise = directiveController.entitySearch('exists');

            expect(resultsPromise).eventually.to.be.fulfilled;
            expect((<any>directiveController).entitiesPaginator.complexQuery).to.have.been.calledWith({
                title: ['exists']
            });

        });

        it('should be able to auto-complete search for entities and return empty array on failure', () => {

            let resultsPromise = directiveController.entitySearch('not-exists');

            expect(resultsPromise).eventually.to.be.become([]);

        });

        it('should call the change handler when the selected entity has been updated', () => {

            let spyHandler = sinon.spy(directiveController, 'entityChangedHandler');

            let newArticle = common.models.ArticleMock.entity();

            directiveController.selectedEntities[0] = newArticle;

            (<any>directiveController).$scope.$apply();

            expect(spyHandler).to.have.been.calledWith(newArticle);
            spyHandler.restore();

        });

        it('should call the change handler when the selected entity has been removed', () => {

            let stubHandler = sinon.stub(directiveController, 'entityChangedHandler');
            directiveController.selectedEntities = [];

            (<any>directiveController).$scope.$apply();

            expect(stubHandler).to.have.been.calledWith(null);
            stubHandler.restore();

        });



    });

}