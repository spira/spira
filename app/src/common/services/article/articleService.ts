module common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService, private paginationService:common.services.pagination.PaginationService) {
        }

        public static articleFactory(data:any):common.models.Article {
            return new common.models.Article(data);
        }

        public getArticlesPaginator():common.services.pagination.Paginator {

            return this.paginationService
                .getPaginatorInstance('/articles')
                .setModelFactory(ArticleService.articleFactory);
        }

    }

    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



