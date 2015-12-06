namespace app.admin.articles.article.meta {

    describe('Article Meta', () => {

        let notificationService:common.services.notification.NotificationService,
            article:common.models.Article = common.models.ArticleMock.entity(),
            $q:ng.IQService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            MetaController:MetaController,
            usersPaginator = {
                query: sinon.stub()
            };

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

            expect(MetaController.entity).to.be.an.instanceOf(common.models.Article);

        });

        it('should be able to search for authors given a string', () => {

            MetaController.searchUsers('foobar');

            expect(usersPaginator.query).to.have.been.calledWith('foobar');

        });

        it('should be able to change the author of a post', () => {

            let newAuthor = common.models.UserMock.entity();

            MetaController.changeAuthor(newAuthor);

            expect(MetaController.authors).to.deep.equal([newAuthor]);

            expect(MetaController.entity._author).to.deep.equal(newAuthor);

            expect(MetaController.entity.authorId).to.equal(newAuthor.userId);

        });

        it('should null author override and author website when display real author is selected', () => {

            MetaController.entity.authorOverride = 'foobar';
            MetaController.entity.authorWebsite = 'foobar.com';
            MetaController.overrideAuthor = false;

            MetaController.authorDisplay();

            expect(MetaController.entity.authorOverride).to.equal(null);
            expect(MetaController.entity.authorWebsite).to.equal(null);

        });

    });

}