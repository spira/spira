namespace common.directives.markdownEditor {

    interface TestScope extends ng.IRootScopeService {
        testModel: any;
    }

    describe('Markdown editor directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:TestScope
        ;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
            });

        });

        it('should initialise the directive and change the model', () => {

            let testText = "Some **markdown** text";

            $rootScope.testModel = testText;

            let element = $compile(`<markdown-editor ng-model="testModel" spell-checker="false"></markdown-editor>`)($rootScope);

            $rootScope.$digest();

            $(element).find('.editor-toolbar > a').first().click();

            $rootScope.$digest();

            expect($rootScope.testModel).not.to.equal(testText);
        });


    });

}