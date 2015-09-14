namespace app.admin.articles.article.meta {

    describe('Article Meta', () => {

        let notificationService:common.services.notification.NotificationService,
            article:common.models.Article,
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

    });

}