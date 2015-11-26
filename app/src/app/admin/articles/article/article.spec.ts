namespace app.admin.articles.article {

    describe('Article (Admin)', () => {

        let article:common.models.Article = common.models.ArticleMock.entity(),
            newArticle:common.models.Article = common.models.ArticleMock.entity({
                title:'new article'
            }),
            notificationService:common.services.notification.NotificationService,
            $q:ng.IQService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:app.admin.ICommonStateParams = <app.admin.ICommonStateParams> {
                id: undefined,
                newEntity: true
            },
            articleService:common.services.article.ArticleService,
            ArticleController:app.admin.articles.article.ArticleController,
            loggedInUser:common.models.User = common.models.UserMock.entity(),
            userService:common.services.user.UserService,
            groupTags:common.models.Tag[] = common.models.TagMock.collection(2);

        article._metas = [common.models.MetaMock.entity({
            metaableId: article.postId
        })];

        newArticle._metas = [common.models.MetaMock.entity({
            metaableId: newArticle.postId
        })];

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _$q_, _articleService_, _userService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                $q = _$q_;
                articleService = _articleService_;
                userService = _userService_;

                articleService.save = sinon.stub().returns($q.when(true));
                articleService.getModel = sinon.stub().returns(article);
                articleService.newArticle = sinon.stub().returns(newArticle);

                userService.getAuthUser = sinon.stub().returns(loggedInUser);
                userService.getUsersPaginator = sinon.stub().returns({
                    setCount: sinon.stub()
                });

                ArticleController = $controller(app.admin.articles.article.namespace + '.controller', {
                    $stateParams: $stateParams,
                    notificationService: notificationService,
                    article: article,
                    articleService: articleService,
                    groupTags: groupTags
                });
            });

            sinon.spy(notificationService, 'toast');

        });

        afterEach(() => {

            (<any>notificationService).toast.restore();

        });

        it('should be able to save a new article', () => {

            ArticleController.save();

            $scope.$apply();

            expect(articleService.save).to.have.been.calledWith(article);

            expect(notificationService.toast).to.have.been.calledOnce;

        });

        it('should be able to resolve article (new)', () => {

            $stateParams.id = 'new';

            let retrievedArticle = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams, userService);

            expect(articleService.newArticle).to.have.been.calledWith(loggedInUser);

            expect(retrievedArticle).to.be.an.instanceOf(common.models.Article);

            expect(retrievedArticle.title).to.equal('new article');

        });

        it('should be able to resolve article (existing)', () => {

            $stateParams.id = 'foobar';

            let retrievedArticle = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams, userService);

            expect(retrievedArticle).to.be.an.instanceOf(common.models.Article);

            expect(articleService.getModel).to.have.been.called;

        });

        it('should be able to resolve users paginator', () => {

            (<any>ArticleConfig.state.resolve).usersPaginator(userService);

            expect(userService.getUsersPaginator).to.have.been.called;

        });

    });

}