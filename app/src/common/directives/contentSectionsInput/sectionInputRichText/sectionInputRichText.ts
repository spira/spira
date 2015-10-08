namespace common.directives.contentSectionsInput.sectionInputRichText {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputRichText';

    class SectionInputRichTextController {

        public section:common.models.Section;
        public richTextForm:ng.IFormController;

        static $inject = [];
        constructor(){

        }

    }

    class SectionInputRichTextDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/contentSectionsInput/sectionInputRichText/sectionInputRichText.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputRichTextController';
        public controller = SectionInputRichTextController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputRichTextDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputRichText', SectionInputRichTextDirective.factory())
    ;


}