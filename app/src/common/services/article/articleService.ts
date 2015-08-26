namespace common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q'];

        private cachedPaginator:common.services.pagination.Paginator;

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService, private paginationService:common.services.pagination.PaginationService, private $q:ng.IQService) {
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

            return this.ngRestAdapter.get('/articles/'+identifier, {
                'With-Nested' : 'permalinks, metas, tags'
            })
                .then((res) => ArticleService.articleFactory(res.data));

        }

        /**
         * Save the article
         * @param article
         * @param newArticle
         * @returns ng.IPromise<common.models.Article>
         */
        public saveArticle(article:common.models.Article, newArticle:boolean = false):ng.IPromise<common.models.Article>{

            let method = newArticle? 'put' : 'patch';

            let changes = (<common.decorators.IChangeAwareDecorator>article).getChanged();

            return this.ngRestAdapter[method]('/articles/'+article.articleId, changes)
                .then(() => this.saveArticleTags(article))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>article).resetChangedProperties(); //reset so next save only saves the changed ones
                    return article;
                });

        }

        private saveArticleTags(article:common.models.Article):ng.IPromise<common.models.Tag[]|boolean>{

            let changes:any = (<common.decorators.IChangeAwareDecorator>article).getChanged();

            if (!_.has(changes, '_tags')){
                return this.$q.when(false);
            }

            return this.ngRestAdapter.put('/articles/'+article.articleId+'/tags', changes._tags)
                .then(() => {
                    return article._tags;
                });

        }

    }

    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



