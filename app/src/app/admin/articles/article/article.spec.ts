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
            groupTags:common.models.Tag[] = common.models.TagMock.collection(2),
            tagService:common.services.tag.TagService,
            $mdDialog:ng.material.IDialogService,
            $state:ng.ui.IStateService;

        article._metas = [common.models.MetaMock.entity({
            metaableId: article.postId
        })];

        newArticle._metas = [common.models.MetaMock.entity({
            metaableId: newArticle.postId
        })];

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _$q_, _articleService_, _userService_, _tagService_, _$mdDialog_, _$state_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                $q = _$q_;
                articleService = _articleService_;
                userService = _userService_;
                tagService = _tagService_;
                $mdDialog = _$mdDialog_;
                $state = _$state_;

                articleService.save = sinon.stub().returns($q.when(true));
                articleService.getModel = sinon.stub().returns($q.when(article));
                articleService.newEntity = sinon.stub().returns(newArticle);

                userService.getAuthUser = sinon.stub().returns(loggedInUser);
                userService.getUsersPaginator = sinon.stub().returns({
                    setCount: sinon.stub()
                });

                tagService.getTagCategories = sinon.stub().returns(common.models.TagMock.collection(5));

                $state.go = sinon.stub();

                ArticleController = $controller(app.admin.articles.article.namespace + '.controller', {
                    $stateParams: $stateParams,
                    notificationService: notificationService,
                    article: article,
                    articleService: articleService,
                    groupTags: groupTags,
                    $mdDialog: $mdDialog,
                    $state: $state,
                    $scope: $scope
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

            expect(articleService.newEntity).to.have.been.calledWith(loggedInUser);

            expect(retrievedArticle).to.be.an.instanceOf(common.models.Article);

            expect(retrievedArticle.title).to.equal('new article');

        });

        it('should be able to resolve article (existing)', () => {

            sinon.spy(articleService, 'hydrateMetaCollection');

            $stateParams.id = 'foobar';

            let retrievedArticle = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams, userService);

            $scope.$apply();

            expect(articleService.getModel).to.have.been.called;

            expect(articleService.hydrateMetaCollection).to.have.been.calledWith(article);

            expect(retrievedArticle).to.eventually.be.an.instanceOf(common.models.Article);

            (<any>articleService.hydrateMetaCollection).restore();

        });

        it('should be able to resolve users paginator', () => {

            (<any>ArticleConfig.state.resolve).usersPaginator(userService);

            expect(userService.getUsersPaginator).to.have.been.called;

        });

        it('should be able to resolve group tags', () => {

            (<any>ArticleConfig.state.resolve).groupTags(tagService, articleService);

            expect(tagService.getTagCategories).to.be.calledWith(articleService);

        });

        it('should be able to toggle preview', () => {

            ArticleController.showPreview = false;

            ArticleController.togglePreview();

            expect(ArticleController.showPreview).to.be.true;

            ArticleController.togglePreview();

            expect(ArticleController.showPreview).to.be.false;

        });

        it('should be able to remove an article', () => {

            $mdDialog.show = sinon.stub().returns($q.when(true));
            $mdDialog.hide = sinon.stub();
            articleService.removeModel = sinon.stub().returns($q.when(true));

            ArticleController.remove();

            expect($mdDialog.show).to.be.called;

            $scope.$apply();

            expect($mdDialog.hide).to.be.called;

            expect(articleService.removeModel).to.be.calledWith(article);

            $scope.$apply();

            expect(notificationService.toast).to.be.calledWith('Deleted');
            expect($state.go).to.be.calledWith((<any>ArticleController).getListingState());
        });

        describe('Dirty form navigation prompt', () => {

            let toState = {
                name:'foobar'
            };

            let toParams = {
                option:'foobar'
            };

            it('should prompt when attempting to navigate away with a dirty form and navigate on confirm', () => {

                ArticleController.entityForm = global.FormControllerMock.getMock(); // $dirty is true

                $mdDialog.show = sinon.stub().returns($q.when(true));

                $scope.$broadcast('$stateChangeStart', toState, toParams);

                expect($mdDialog.show).to.be.called;

                $scope.$apply();

                expect($state.go).to.be.calledWith(toState.name, toParams);

            });

            it('should not navigate away with cancel', () => {

                ArticleController.entityForm = global.FormControllerMock.getMock();

                $mdDialog.show = sinon.stub().returns($q.reject());

                $scope.$broadcast('$stateChangeStart', toState, toParams);

                expect($mdDialog.show).to.be.called;

                $scope.$apply();

                expect($state.go).to.not.be.called;

            });

            it('should not show the prompt if the form is not dirty', () => {

                ArticleController.entityForm = global.FormControllerMock.getMock({
                    $dirty:false
                });

                $mdDialog.show = sinon.stub().returns($q.when(true));

                $scope.$broadcast('$stateChangeStart', toState, toParams);

                expect($mdDialog.show).to.not.be.called;

            });

        });

    });

}