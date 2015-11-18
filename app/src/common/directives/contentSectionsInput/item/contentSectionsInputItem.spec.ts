namespace common.directives.contentSectionsInput.item {

    interface TestScope extends ng.IRootScopeService {
        ContentSectionsInputItemController: ContentSectionsInputItemController;
        section: any;
    }

    describe('Content sections directive item', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: ContentSectionsInputItemController,
            $q:ng.IQService,
            parentControllerStub = {
                registerSettingsBindings: sinon.stub(),
            },
            mockBindingSettings = {
                templateUrl: '/some/path/to/a/template.tpl.html',
                controller: angular.noop,
                controllerAs: 'SomeController',
            };

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

                let element = angular.element(`
                    <content-sections-input-item
                        section="section">
                    </content-sections-input-item>
                `);

                element.data('$contentSectionsInputSetController', parentControllerStub);

                compiledElement = $compile(element)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).ContentSectionsInputItemController;

                (<any>directiveController).$mdBottomSheet.show = sinon.stub().returns($q.when(true));
                (<any>directiveController).$mdBottomSheet.cancel = sinon.stub().returns(undefined);
            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).children().first().hasClass('content-sections-input-item')).to.be.true;
        });

        it('should be able to register settings binding to the controller', () => {


            directiveController.registerSettingsBindings(mockBindingSettings);

            expect((<any>directiveController).childControllerSettings).to.deep.equal(mockBindingSettings);

        });

        it('should be able to prompt a bottomsheet of child directive settings to pop when requested', () => {

            directiveController.toolbarOpen = false;
            directiveController.registerSettingsBindings(mockBindingSettings);

            let sheetPromise = directiveController.toggleSettings(global.MouseEventMock.getMock());

            expect(directiveController.toolbarOpen).to.be.true;

            directiveScope.$digest();

            expect(directiveController.toolbarOpen).to.be.false;

            expect((<any>directiveController).$mdBottomSheet.show).to.have.been.calledWith(sinon.match({
                templateUrl: mockBindingSettings.templateUrl,
                controllerAs: mockBindingSettings.controllerAs,
                controller: sinon.match.func,
            }));

            expect(sheetPromise).eventually.to.be.fulfilled;
            (<any>directiveController).$mdBottomSheet.show.reset();

        });

        it('should be able to prompt a bottom sheet when there is no child directive with settings', () => {
            directiveController.toolbarOpen = false;
            (<any>directiveController).childControllerSettings = null;//ensure there is no child bound

            let sheetPromise = directiveController.toggleSettings(global.MouseEventMock.getMock());

            directiveScope.$digest();

            expect((<any>directiveController).$mdBottomSheet.show).to.have.been.calledWith(sinon.match({
                templateUrl: 'templates/common/directives/contentSectionsInput/item/dummySettingsMenu.tpl.html',
            }));

            expect(sheetPromise).eventually.to.be.fulfilled;
            (<any>directiveController).$mdBottomSheet.show.reset();

        });

        it('should be able to dismiss an opened dialog', () => {

            directiveController.toolbarOpen = true;
            let closeResult = directiveController.toggleSettings(global.MouseEventMock.getMock());

            directiveScope.$digest();

            expect((<any>directiveController).$mdBottomSheet.cancel).to.have.been.called;

            expect(closeResult).to.equal(undefined);

            (<any>directiveController).$mdBottomSheet.cancel.reset();

        });

        it('should be able to merge properties of an instantiated controller with settings controller', () => {

            let testController = new ContentSectionsInputItemController(<any>sinon.stub(), <any>sinon.stub(), <any>sinon.stub());

            let settingsController = new SettingsSheetController(testController);

            expect(settingsController).to.have.property('toolbarOpen');
            expect((<any>settingsController).toolbarOpen).to.be.false;

        });


    });

}