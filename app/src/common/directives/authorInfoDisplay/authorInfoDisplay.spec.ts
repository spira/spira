namespace common.directives.authorInfoDisplay {

    interface TestScope extends ng.IRootScopeService {
        AuthorInfoDisplayController:AuthorInfoDisplayController;
        testAuthor:common.models.User;
    }

    describe('Author Info Display Directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: AuthorInfoDisplayController,
            author = common.models.UserMock.entity();

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
            });

            //only initialise the directive once to speed up the testing
            if (!directiveController) {

                directiveScope = <TestScope>$rootScope.$new();

                directiveScope.testAuthor = author;

                let element = angular.element(`
                    <author-info-display author="testAuthor">
                    </author-info-display>
                `);

                compiledElement = $compile(element)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).AuthorInfoDisplayController;
            }

        });

        describe('Initialization', () => {

            it('should initialise the directive', () => {

                expect($(compiledElement).hasClass('author-info-display-directive')).to.be.true;

                expect(directiveController.author).to.deep.equal(author);

            });

        });

    });

}