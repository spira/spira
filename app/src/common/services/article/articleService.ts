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
         * @param exists
         */
        public static articleFactory(data:any, exists:boolean = false):common.models.Article {
            return new common.models.Article(data, exists);
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
                'With-Nested' : 'articlePermalink, articleMeta, tag'
            })
                .then((res) => ArticleService.articleFactory(res.data, true));

        }

        /**
         * Save the article with all the nested entities too
         * @param article
         * @returns {IPromise<common.models.Article>}
         */
        public saveArticleWithRelated(article:common.models.Article):ng.IPromise<common.models.Article>{

            return this.saveArticle(article)
                .then(() => this.saveRelatedEntities(article))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>article).resetChangedProperties(); //reset so next save only saves the changed ones
                    article.setExists(true);
                    return article;
                })
            ;

        }

        /**
         * Save the article
         * @param article
         * @returns ng.IPromise<common.models.Article>
         */
        public saveArticle(article:common.models.Article):ng.IPromise<common.models.Article|boolean>{

            let method = article.exists() ? 'patch' : 'put';

            let saveData = article.getAttributes();

            if (article.exists()) {
                saveData = (<common.decorators.IChangeAwareDecorator>article).getChanged();
            }

            if (_.size(saveData) == 0) { //if there is nothing to save, don't make an api call
                return this.$q.when(true);
            }

            return this.ngRestAdapter[method]('/articles/'+article.articleId, saveData)
                .then(() => article);

        }

        /**
         * Removes a meta tag
         * @param article
         * @param metaTagName
         * @returns {IPromise<common.models.Article>}
         */
        public removeMetaTag(article:common.models.Article, metaTagName:string):ng.IPromise<any>{
            return this.ngRestAdapter
                .skipInterceptor()
                .remove('/articles/' + article.articleId + '/meta/' + metaTagName);
        }

        /**
         * Save all the related entities concurrently
         * @param article
         * @returns {IPromise<any[]>}
         */
        private saveRelatedEntities(article:common.models.Article):ng.IPromise<any> {

            return this.$q.all([ //save all related entities
                this.saveArticleTags(article),
                this.saveArticleMetas(article)
            ]);

        }

        /**
         * Save the tags to the article.
         * @param article
         * @returns {any}
         */
        private saveArticleTags(article:common.models.Article):ng.IPromise<common.models.Tag[]|boolean>{

            let tagData = _.clone(article._tag);

            if (article.exists()){

                let changes:any = (<common.decorators.IChangeAwareDecorator>article).getChanged(true);
                if (!_.has(changes, '_tag')){
                    return this.$q.when(false);
                }
            }

            return this.ngRestAdapter.put('/articles/'+article.articleId+'/tags', tagData)
                .then(() => {
                    return article._tag;
                });

        }

        /**
         * Save article metas
         * @param article
         * @returns {any}
         */
        private saveArticleMetas(article:common.models.Article):ng.IPromise<common.models.ArticleMeta[]|boolean> {

            let metaData = _.clone(article._articleMeta);

            if (article.exists()){

                let changes:any = (<common.decorators.IChangeAwareDecorator>article).getChanged(true);

                if (!_.has(changes, '_articleMeta')){
                    return this.$q.when(false);
                }
            }

            return this.ngRestAdapter.put(`/articles/${article.articleId}/meta`, metaData)
                .then(() => {
                    _.forEach(article._articleMeta, (metaTag) => { // All tags have been saved, none of them are new
                        metaTag.newTag = false;
                    });
                    return article._articleMeta;
                });
        }

    }

    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



