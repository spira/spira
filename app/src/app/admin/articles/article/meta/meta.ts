namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class AuthorContactChip {
        public id:string = undefined;
        public name:string = undefined;
        public image:string = undefined;
        public email:string = undefined;
    }

    export class MetaController {

        static $inject = ['article', 'notificationService'];

        public author:AuthorContactChip = undefined;

        constructor(
            public article:common.models.Article,
            private notificationService:common.services.notification.NotificationService
        ) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}