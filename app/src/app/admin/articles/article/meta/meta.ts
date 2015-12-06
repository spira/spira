namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController extends app.admin.AbstractMetaController<common.models.Article> {

        public authors:common.models.User[];

        static $inject = ['article', 'notificationService', 'usersPaginator'];

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}