namespace common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService'];

        private cachedPaginator:common.services.pagination.Paginator;

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
         * Get a new article with no values and a set uuid
         * @returns {common.models.Article}
         */
        public newArticle():common.models.Article {

            return new common.models.Article({
                articleId: this.ngRestAdapter.uuid(),
            });

        }

        /**
         * Get the article paginator
         * @returns {Paginator}
         */
        public getArticlesPaginator():common.services.pagination.Paginator {

            //cache the paginator so subsequent requests can be collection length-aware
            if (!this.cachedPaginator){
                this.cachedPaginator = this.paginationService
                    .getPaginatorInstance('/articles')
                    .setModelFactory(ArticleService.articleFactory);
            }

            return this.cachedPaginator;
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



