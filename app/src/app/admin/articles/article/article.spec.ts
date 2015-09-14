namespace app.admin.articles.article {

    describe('Article', () => {

        let seededChance = new Chance(1),
            getArticle = (title?:string):common.models.Article => {

                if(_.isEmpty(title)) {
                    title = seededChance.sentence();
                }

                return new common.models.Article({
                    title: title,
                    body: seededChance.paragraph(),
                    permalink: title.replace(' ', '-'),
                    _articleMeta: []
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
                    return getArticle(identifier);
                },
                newArticle:() => {
                    return getArticle('newArticle');
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

        it('should be able to resolve article (new)', () => {

            $stateParams.permalink = 'new';

            let article = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams);

            expect(article).to.be.an.instanceOf(common.models.Article);

            expect(article.title).to.equal('newArticle');

        });

        it('should be able to resolve article (existing)', () => {

            $stateParams.permalink = 'foobar';

            let article = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams);

            expect(article).to.be.an.instanceOf(common.models.Article);

            expect(article.title).to.equal('foobar');

        });

        it('should be able to resolve articleMetaTags', () => {

            let article:common.models.Article = getArticle();

            article._articleMeta.push(new common.models.ArticleMeta({
                metaName: 'title',
                metaContent: 'foo'
            }));

            article._articleMeta.push(new common.models.ArticleMeta({
                metaName: 'keyword',
                metaContent: 'bar'
            }));

            expect(_.cloneDeep(article._articleMeta)).to.deep.equal([
                {metaName: 'title', metaContent: 'foo'},
                {metaName: 'keyword', metaContent: 'bar'}
            ]);

            (<any>ArticleConfig.state.resolve).articleMetaTags(article);

            expect(_.cloneDeep(article._articleMeta)).to.deep.equal([
                {metaName: 'name', metaContent: ''},
                {metaName: 'description', metaContent: ''},
                {metaName: 'keyword', metaContent: 'bar'},
                {metaName: 'canonical', metaContent: ''},
                {metaName: 'title', metaContent: 'foo'}
            ]);

        });

    });

}