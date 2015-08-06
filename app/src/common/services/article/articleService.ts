module common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService, private paginationService:common.services.pagination.PaginationService) {
        }

        /**
         * Get an instance of the Article given data
         * @param data
         * @returns {common.models.Article}
         */
        public static articleFactory(data:any):common.models.Article {
            return new common.models.Article(data);
        }

        /**
         * Get the article paginator
         * @returns {Paginator}
         */
        public getArticlesPaginator():common.services.pagination.Paginator {

            return this.paginationService
                .getPaginatorInstance('/articles')
                .setModelFactory(ArticleService.articleFactory);
        }

        /**
         * Get an Article given an identifier (uuid or permalink)
         * @param identifier
         * @returns {IPromise<common.models.Article>}
         */
        public getArticle(identifier:string):ng.IPromise<common.models.Article> {

            return this.ngRestAdapter.get('/articles/'+identifier)
                .then((res) => ArticleService.articleFactory(res.data));

        }

    }

    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



