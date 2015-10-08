namespace common.directives.contentSectionsInput.sectionInputBlockquote {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputBlockquote';

    class SectionInputBlockquoteController {

        public section:common.models.Section;
        public blockquoteForm:ng.IFormController;

        static $inject = [];
        constructor(){

        }

    }

    class SectionInputBlockquoteDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/contentSectionsInput/sectionInputBlockquote/sectionInputBlockquote.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputBlockquoteController';
        public controller = SectionInputBlockquoteController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputBlockquoteDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputBlockquote', SectionInputBlockquoteDirective.factory())
    ;


}