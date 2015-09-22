namespace app.guest.articles.article.comments {

    export const namespace = 'app.guest.articles.article.comments';

    export class CommentsController {

        static $inject = ['article', 'user', 'articleService'];

        public newComment:string = '';

        constructor(
            public article:common.models.Article,
            public user:common.models.User,
            private articleService:common.services.article.ArticleService
        ) {
        }

        /**
         * Save a new comment
         * @returns {any}
         */
        public save() {

            let comment = new common.models.ArticleComment({
                body: this.newComment,
                createdAt: moment()
            });

            this.articleService.saveComment(this.article.articleId, comment)
                .then((res) => {
                    debugger;
                });

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', CommentsController);

}