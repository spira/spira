namespace app.admin.articles.article {

    describe('Article', () => {

        let seededChance = new Chance(1),
            testMeta:common.models.ArticleMeta = new common.models.ArticleMeta({
                articleId: undefined,
                id: seededChance.guid(),
                metaName: 'title',
                metaContent: 'foobar'
            }),
            getArticle = (title?:string):common.models.Article => {

                if(_.isEmpty(title)) {
                    title = seededChance.sentence();
                }

                return new common.models.Article({
                    title: title,
                    body: seededChance.paragraph(),
                    permalink: title.replace(' ', '-'),
                    _articleMetas: [testMeta]
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
                saveArticleWithRelated:(article:common.models.Article) => {
                    return $q.when(true);
                },
                getArticle:(identifier:string) => {
                    return $q.when(getArticle(identifier));
                },
                newArticle:() => {
                    return getArticle('newArticle');
                },
                hydrateMetaFromTemplate:(article:common.models.Article, template:string[]):common.models.ArticleMeta[] => {
                    return [
                        new common.models.ArticleMeta({metaName:'foobarfoo', metaContent:'barfoo'})
                    ];
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
                    articleService: articleService,
                    articleMetaTags: []

                });
            });

            sinon.spy(notificationService, 'toast');
            sinon.spy(articleService, 'saveArticleWithRelated');
            sinon.spy(articleService, 'hydrateMetaFromTemplate');

        });

        afterEach(() => {

            (<any>notificationService).toast.restore();
            (<any>articleService).saveArticleWithRelated.restore();
            (<any>articleService).hydrateMetaFromTemplate.restore();

        });

        it('should be able to save a new article', () => {

            let article:common.models.Article = getArticle();

            ArticleController.article = article;

            ArticleController.save();

            $scope.$apply();

            expect(articleService.saveArticleWithRelated).to.have.been.calledWith(article);

            expect(notificationService.toast).to.have.been.calledOnce;

        });

        it('should be able to resolve article (new)', () => {

            $stateParams.permalink = 'new';

            let article = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams);

            expect(article).to.be.an.instanceOf(common.models.Article);

            expect(article.title).to.equal('newArticle');

        });

        it('should be able to resolve article (existing)', () => {

            $stateParams.permalink = 'foobar';

            let article = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams);

            $rootScope.$apply();

            expect(article).eventually.to.be.an.instanceOf(common.models.Article);

            expect(articleService.hydrateMetaFromTemplate).to.have.been.calledWith(sinon.match.instanceOf(common.models.Article));

        });

    });

}