namespace app.guest.articles.article.comments {

    describe('Article Comments', () => {

        let notificationService:common.services.notification.NotificationService,
            articleService:common.services.article.ArticleService,
            article:common.models.Article = common.models.ArticleMock.entity(),
            user:common.models.User = common.models.UserMock.entity(),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            CommentsController:CommentsController,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _articleService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                articleService = _articleService_;
                $q = _$q_;

                notificationService.toast = sinon.stub();

                CommentsController = $controller(app.guest.articles.article.comments.namespace + '.controller', {
                    article: article,
                    user: user,
                    articleService: articleService,
                    notificationService: notificationService
                });
            });

        });

        it('should have an article injected into it', () => {

            expect(CommentsController.article).to.be.an.instanceOf(common.models.Article);

        });

        it('should have the user injected into it', () => {

            expect(CommentsController.user).to.be.an.instanceOf(common.models.User);

        });

        it('should be able to save a new comment', () => {

            articleService.saveComment = sinon.stub().returns($q.when(true));

            let commentToAdd = common.models.ArticleCommentMock.entity();

            CommentsController.newComment = commentToAdd;

            CommentsController.save();

            expect(articleService.saveComment).to.be.calledWith(article, commentToAdd);

            expect(CommentsController.newComment).to.not.deep.equal(commentToAdd);

            expect(notificationService.toast).to.be.calledOnce;

        });

    });

}