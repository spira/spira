namespace common.directives.contentSectionsInput.sectionInputImage {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputImage';

    class SectionInputImageController {

        public selectedIndex:number = 0;
        public section:common.models.Section<common.models.sections.Image>;
        public imageForm:ng.IFormController;
        public alignmentOptions:common.models.sections.IAlignmentOption[];
        public sizeOptions:common.models.sections.ISizeOption[];

        static $inject = [];
        constructor(){

            this.alignmentOptions = common.models.sections.Image.alignmentOptions;
            this.sizeOptions = common.models.sections.Image.sizeOptions;
        }

    }

    class SectionInputImageDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/contentSectionsInput/sectionInputImage/sectionInputImage.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputImageController';
        public controller = SectionInputImageController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputImageDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputImage', SectionInputImageDirective.factory())
    ;


}