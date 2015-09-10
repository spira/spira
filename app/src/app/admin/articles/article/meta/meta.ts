namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class AuthorContactChip {
        public id:string = undefined;
        public name:string = undefined;
        public image:string = undefined;
        public email:string = undefined;
    }

    export class MetaController {

        static $inject = ['article', 'articleService', 'notificationService', 'userService'];

        public author:AuthorContactChip = undefined;

        constructor(
            public article:common.models.Article,
            private articleService:common.services.article.ArticleService,
            private notificationService:common.services.notification.NotificationService
        ) {
        }

        public authorSearch(query:string):void {

        }
    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}