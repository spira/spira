namespace app.guest.articles.article.comments {

    export const namespace = 'app.guest.articles.article.comments';

    export class CommentsController {

        static $inject = ['article', 'user', 'articleService', 'notificationService'];

        public newComment:common.models.Comment;

        public newCommentForm:ng.IFormController;

        constructor(
            public article:common.models.Article,
            public user:common.models.User,
            private articleService:common.services.article.ArticleService,
            private notificationService:common.services.notification.NotificationService
        ) {
            this.newComment = new common.models.Comment({_author:this.user});
        }

        /**
         * Save a new comment
         * @returns {any}
         */
        public save() {

            this.articleService.saveComment(this.article, this.newComment)
                .then((comment) => {
                    this.article._comments.push(comment);
                    this.newComment = new common.models.Comment({_author:this.user});
                    this.newCommentForm.$setPristine();
                    this.newCommentForm.$setUntouched();
                    this.notificationService.toast('Comment successfully added').pop();
                })
                .catch((error) => {
                    this.notificationService.toast('An error has occurred saving your comment, please try again').pop();
                });

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', CommentsController)

}