namespace app.admin.articles.article.meta {

    describe('Article Meta', () => {

        let seededChance = new Chance(1),
            notificationService:common.services.notification.NotificationService,
            article:common.models.Article = new common.models.Article({
                title: 'foo',
                body: seededChance.paragraph(),
                permalink: 'foo'
            }),
            $q:ng.IQService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            MetaController:MetaController,
            usersPaginator = {
                query: sinon.stub()
            };

        article._metas = [
            new common.models.Meta({
                metaName: 'name',
                metaContent: 'foo',
                metaableId: article.articleId
            }),
            new common.models.Meta({
                metaName: 'description',
                metaContent: 'bar',
                metaableId: article.articleId
            }),
            new common.models.Meta({
                metaName: 'keyword',
                metaContent: 'foo, bar',
                metaableId: article.articleId
            }),
            new common.models.Meta({
                metaName: 'canonical',
                metaContent: 'https://foo.bar.com',
                metaableId: article.articleId
            })
        ];

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _notificationService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                notificationService = _notificationService_;
                $q = _$q_;

                MetaController = $controller(app.admin.articles.article.meta.namespace + '.controller', {
                    article: article,
                    notificationService: notificationService,
                    usersPaginator: usersPaginator
                });
            });

        });

        it('should have an article injected into it', () => {

            expect(MetaController.article).to.be.an.instanceOf(common.models.Article);

        });

        it('should be able to search for authors given a string', () => {

            MetaController.searchUsers('foobar');

            expect(usersPaginator.query).to.have.been.calledWith('foobar');

        });

        it('should be able to change the author of a post', () => {

            let newAuthor = common.models.UserMock.entity();

            MetaController.changeAuthor(newAuthor);

            expect(MetaController.authors).to.deep.equal([newAuthor]);

            expect(MetaController.article._author).to.deep.equal(newAuthor);

            expect(MetaController.article.authorId).to.equal(newAuthor.userId);

        });

    });

}