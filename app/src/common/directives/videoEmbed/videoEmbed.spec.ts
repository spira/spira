namespace common.directives.videoEmbed {

    interface TestScope extends ng.IRootScopeService {
        testModel: any;
    }

    describe('Video embed directive', () => {

        let $compile:ng.ICompileService,
            $rootScope:TestScope,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery
        ;

        beforeEach(()=> {

            module('app');

            inject((_$compile_, _$rootScope_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
            });


                directiveScope = <TestScope>$rootScope.$new();

                compiledElement = $compile(`
                    <video-embed
                        provider="youtube"
                        video-id="dQw4w9WgXcQ"
                    ></video-embed>
                `)(directiveScope);

                $rootScope.$digest();
        });

        it('should initialise the directive, with an embedded iframe', () => {

            expect($(compiledElement).hasClass('ng-scope')).to.be.true;
            expect($(compiledElement).find('iframe').attr('src')).to.equal('https://www.youtube.com/embed/dQw4w9WgXcQ?modestbranding=1');
        });


    });

}