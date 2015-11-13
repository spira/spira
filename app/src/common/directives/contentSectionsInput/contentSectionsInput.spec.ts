namespace common.directives.contentSectionsInput {

    interface TestScope extends ng.IRootScopeService {
        testSectionsModel: any;
        testSectionUpdated(event, section):void;
        ContentSectionsInputSetController: set.ContentSectionsInputSetController;
    }

    describe('Content sections directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: set.ContentSectionsInputSetController,
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

                directiveScope.testSectionsModel = [
                    common.models.sections.BlockquoteMock.entity(),
                    common.models.sections.MediaMock.entity(),
                    common.models.sections.RichTextMock.entity(),
                    common.models.sections.PromoMock.entity(),
                ];

                directiveScope.testSectionUpdated = sinon.spy();

                compiledElement = $compile(`
                    <content-sections-input-set
                        ng-model="testSectionsModel"
                        on-section-update="testSectionUpdated(event, section)"
                    ></content-sections-input-set>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).ContentSectionsInputSetController;

                let stubbedShow = sinon.stub();
                stubbedShow.onCall(0).returns($q.when(true));
                (<any>directiveController).$mdDialog.show = stubbedShow;

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('content-sections-input')).to.be.true;
        });

        it('should be able to add a new section type', () => {

            let currentSectionCount = directiveController.sections.length;

            directiveController.addSectionType(common.models.sections.RichText.contentType);

            $rootScope.$digest();

            expect(directiveController.sections).to.have.length(currentSectionCount + 1);
            expect(directiveScope.testSectionUpdated).to.have.been.calledWith('added', sinon.match.instanceOf(common.models.Section));
            (<Sinon.SinonSpy>directiveScope.testSectionUpdated).reset();
        });

        it('should be able to remove a section', (done) => {

            let currentSectionCount = directiveController.sections.length;

            let removePromise = directiveController.removeSection(_.last(directiveController.sections));

            $rootScope.$digest();

            expect(removePromise).eventually.to.be.fulfilled;
            removePromise.then(() => {
                expect(directiveController.sections).to.have.length(currentSectionCount - 1);
                expect(directiveScope.testSectionUpdated).to.have.been.calledWith('deleted', sinon.match.instanceOf(common.models.Section));
                (<Sinon.SinonSpy>directiveScope.testSectionUpdated).reset();
                done();
            });

        });

        it('should be able to move a section down', () => {

            let section = _.first(directiveController.sections);

            directiveController.moveSection(section, false);

            $rootScope.$digest();

            expect(directiveController.sections[1].sectionId).to.equal(section.sectionId);
            expect(directiveScope.testSectionUpdated).to.have.been.calledWith('moved');
            (<Sinon.SinonSpy>directiveScope.testSectionUpdated).reset();

        });

        it('should be able to move a section up', () => {

            let section = _.last(directiveController.sections);

            directiveController.moveSection(section);
            expect(directiveController.sections[directiveController.sections.length-2].sectionId).to.equal(section.sectionId);
            expect(directiveScope.testSectionUpdated).to.have.been.calledWith('moved');
            (<Sinon.SinonSpy>directiveScope.testSectionUpdated).reset();

        });

        it('should initialise sections as empty array if falsy model is provided', () => {

            let scope:TestScope = <TestScope>$rootScope.$new();

            let compiled = $compile(`
                    <content-sections-input-set
                        ng-model="testSectionsModel"
                        on-section-update="testSectionUpdated(event, section)"
                    ></content-sections-input-set>
                `)(scope);

            $rootScope.$digest();

            directiveController = (<TestScope>compiled.isolateScope()).ContentSectionsInputSetController;

            expect(directiveController.sections).to.be.instanceOf(Array);
            expect(directiveController.sections).to.be.empty;

        });


    });

}