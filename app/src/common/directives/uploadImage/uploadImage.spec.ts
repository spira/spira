namespace common.directives.uploadImage {

    interface TestScope extends ng.IRootScopeService {
        testImage: common.models.Image;
        UploadImageController: UploadImageController;
    }

    describe.only('Upload image directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:ng.IRootScopeService,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            directiveController: UploadImageController,
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

                compiledElement = $compile(`
                    <upload-image
                        ng-model="testImage">
                    </upload-image>
                `)(directiveScope);

                $rootScope.$digest();

                directiveController = (<TestScope>compiledElement.isolateScope()).UploadImageController;

            }

        });

        it('should initialise the directive', () => {

            expect($(compiledElement).hasClass('upload-image')).to.be.true;
        });


    });

}