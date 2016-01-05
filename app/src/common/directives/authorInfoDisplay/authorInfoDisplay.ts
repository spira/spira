namespace common.directives.authorInfoDisplay {

    export const namespace = 'common.directives.authorInfoDisplay';

    export class AuthorInfoDisplayController {

        static $inject = [];

        public author:common.models.User;
        public recentArticles:common.models.Article;

        constructor(
        ) {
        }

    }

    class AuthorInfoDisplayDirective implements ng.IDirective {

        public restrict = 'E';
        public require =  ['AuthorInfoDisplayController'];
        public templateUrl = 'templates/common/directives/authorInfoDisplay/authorInfoDisplay.tpl.html';
        public replace = true;
        public scope = {
            author: '='
        };

        public controllerAs = 'AuthorInfoDisplayController';
        public controller = AuthorInfoDisplayController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new AuthorInfoDisplayDirective();
        }
    }

    angular.module(namespace, [])
        .directive('authorInfoDisplay', AuthorInfoDisplayDirective.factory())
    ;


}