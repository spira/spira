namespace common.directives.contentSectionsInput.sectionInputPromo {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputPromo';

    class SectionInputPromoController {

        public section:common.models.Section<common.models.sections.Promo>;
        public promoForm:ng.IFormController;

        static $inject = [];
        constructor(){

        }

    }

    class SectionInputPromoDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/contentSectionsInput/sectionInputPromo/sectionInputPromo.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputPromoController';
        public controller = SectionInputPromoController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputPromoDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputPromo', SectionInputPromoDirective.factory())
    ;


}