namespace app.admin.articles.article {

    describe('Article', () => {

        let seededChance = new Chance(1),
            getArticle = ():common.models.Article => {

                let title = seededChance.sentence();

                return new common.models.Article({
                    title: title,
                    body: seededChance.paragraph(),
                    permalink: title.replace(' ', '-'),
                });

            },
            notificationService:common.services.notification.NotificationService,
            article:common.models.Article,
            $q:ng.IQService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IArticleStateParams = <IArticleStateParams> {
                newArticle:true
            },
            articleService = {
                saveArticleWithRelated:(article:common.models.Article, newArticle:boolean = false) => {
                    return $q.when(true);
                }
            },
            ArticleController:ArticleController;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                $q = _$q_;

                ArticleController = $controller(app.admin.articles.article.namespace + '.controller', {
                    $stateParams: $stateParams,
                    notificationService: notificationService,
                    article: article,
                    articleService: articleService
                });
            });

            sinon.spy(notificationService, 'toast');
            sinon.spy(articleService, 'saveArticleWithRelated');

        });

        afterEach(() => {

            (<any>notificationService).toast.restore();
            (<any>articleService).saveArticleWithRelated.restore();

        });


        it('should be able to save a new article', () => {

            let article:common.models.Article = getArticle();

            ArticleController.article = article;

            ArticleController.save();

            $scope.$apply();

            expect(articleService.saveArticleWithRelated).to.have.been.calledWith(article);

            expect(notificationService.toast).to.have.been.calledOnce;

        });

    });

}