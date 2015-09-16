namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        static $inject = ['article', 'notificationService', 'usersPaginator'];

        public authors:common.models.User[] = [];

        constructor(
            public article:common.models.Article,
            private notificationService:common.services.notification.NotificationService,
            private usersPaginator:common.services.pagination.Paginator
        ) {
            this.authors.push(article._author);
        }

        /**
         * Function called when author is searched for in the author contact chip input
         * @param query
         */
        public searchAuthors(query:string) {

            return [{firstName:"John", email:"email@email.com", avatarImgUrl:"http://lorempixel.com/100/100/people/?80221"},{firstName:"Doe", email:"email@email.com", avatarImgUrl:"http://lorempixel.com/100/100/people/?80221"}];

            //return this.usersPaginator.query(query);

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}