namespace app.admin.articles.article.meta {

    describe('Article Meta', () => {

        let seededChance = new Chance(1),
            notificationService:common.services.notification.NotificationService,
            article:common.models.Article = new common.models.Article({
                title: 'foo',
                body: seededChance.paragraph(),
                permalink: 'foo',
                _articleMetas: [
                    new common.models.ArticleMeta({
                        metaName: 'name',
                        metaContent: 'foo'}),
                    new common.models.ArticleMeta({
                        metaName: 'description',
                        metaContent: 'bar'}),
                    new common.models.ArticleMeta({
                        metaName: 'keyword',
                        metaContent: 'foo, bar'}),
                    new common.models.ArticleMeta({
                        metaName: 'canonical',
                        metaContent: 'https://foo.bar.com'})
                ]
            }),
            $q:ng.IQService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            MetaController:MetaController;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                $q = _$q_;

                MetaController = $controller(app.admin.articles.article.meta.namespace + '.controller', {
                    article: article,
                    notificationService: notificationService
                });
            });

        });

        it('should have an article injected into it', () => {

            expect(MetaController.article).to.be.an.instanceOf(common.models.Article);

        });

    });

}