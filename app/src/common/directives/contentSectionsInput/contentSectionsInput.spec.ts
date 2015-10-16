namespace common.directives.contentSectionsInput {

    interface TestScope extends ng.IRootScopeService {
        testSectionsModel: any;
        testSectionUpdated(event, section):void;
        ContentSectionsInputController: ContentSectionsInputController;
    }

    describe('Content sections directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: common.directives.contentSectionsInput.ContentSectionsInputController
        ;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
            });

            $rootScope.testSectionsModel = [
                common.models.sections.BlockquoteMock.entity(),
                common.models.sections.ImageMock.entity(),
                common.models.sections.RichTextMock.entity(),
                common.models.sections.PromoMock.entity(),
            ];

            $rootScope.testSectionUpdated = sinon.spy();

            compiledElement = $compile(`
            <content-sections-input
                ng-model="testSectionsModel"
                on-section-update="testSectionUpdated(event, section)"
            ></content-sections-input>
            `)($rootScope);

            $rootScope.$digest();

            directiveController = (<TestScope>compiledElement.isolateScope()).ContentSectionsInputController;

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('content-sections-input')).to.be.true;
        });

        it('should be able to add a new section type', () => {


            let currentSectionCount = directiveController.sections.length;

            directiveController.addSectionType(common.models.sections.RichText.contentType);

            $rootScope.$digest();

            expect(directiveController.sections).to.have.length(currentSectionCount + 1);
            expect($rootScope.testSectionUpdated).to.have.been.calledWith('added', sinon.match.instanceOf(common.models.Section));
        });


    });

}