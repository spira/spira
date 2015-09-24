namespace app.guest.articles.article.comments {

    describe('Article Comments', () => {

        let notificationService:common.services.notification.NotificationService,
            articleService:common.services.article.ArticleService,
            article:common.models.Article = common.models.ArticleMock.entity(),
            user:common.models.User = common.models.UserMock.entity(),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            CommentsController:CommentsController,
            $q:ng.IQService,
            commentToAddSuccess = common.models.ArticleCommentMock.entity(),
            commentToAddFailure = common.models.ArticleCommentMock.entity({body:'foobar'});

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _articleService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                articleService = _articleService_;
                $q = _$q_;

                let stub = sinon.stub();
                stub.withArgs(article, commentToAddSuccess).returns($q.when(true));
                stub.withArgs(article, commentToAddFailure).returns($q.reject());

                articleService.saveComment = stub;

                CommentsController = $controller(app.guest.articles.article.comments.namespace + '.controller', {
                    article: article,
                    user: user,
                    articleService: articleService,
                    notificationService: notificationService
                });

                CommentsController.newCommentForm = global.FormControllerMock.getMock();
            });

            sinon.spy(notificationService, 'toast');

        });

        afterEach(() => {

            (<any>notificationService.toast).restore();

        });

        it('should have an article injected into it', () => {

            expect(CommentsController.article).to.be.an.instanceOf(common.models.Article);

        });

        it('should have the user injected into it', () => {

            expect(CommentsController.user).to.be.an.instanceOf(common.models.User);

        });

        it('should be able to save a new comment', () => {

            CommentsController.newComment = commentToAddSuccess;

            CommentsController.save();

            expect(articleService.saveComment).to.be.calledWith(article, commentToAddSuccess);

            $scope.$apply();

            expect(CommentsController.newComment).to.not.deep.equal(commentToAddSuccess);

            expect(notificationService.toast).to.be.calledWithMatch('Comment successfully added');

        });

        it('should show error on save comment fail', () => {

            CommentsController.newComment = commentToAddFailure;

            CommentsController.save();

            expect(articleService.saveComment).to.be.calledWith(article, commentToAddFailure);

            $scope.$apply();

            expect(notificationService.toast).to.be.calledWithMatch('An error has occurred saving your comment, please try again');

        });

    });
}