namespace common.directives.commandWidget {

    interface TestScope extends ng.IRootScopeService {
        CommandWidgetController:CommandWidgetController;
    }

    describe('Command widget Directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: CommandWidgetController;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
            });

            //only initialise the directive once to speed up the testing
            if (!directiveController) {

                directiveScope = <TestScope>$rootScope.$new();

                let element = angular.element(`
                    <command-widget>
                    </command-widget>
                `);

                compiledElement = $compile(element)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).CommandWidgetController;
            }

        });

        describe('Initialization', () => {

            it('should initialise the directive', () => {

                expect($(compiledElement).find('.command-wrap')).to.have.length(1);

            });

        });

    });

}