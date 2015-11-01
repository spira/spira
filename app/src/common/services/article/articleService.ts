namespace common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService extends AbstractApiService implements common.services.IExtendedApiService, common.mixins.SectionableApiService, common.mixins.TaggableApiService {


        //SectionableApiService
        public saveEntitySections: (entity:mixins.SectionableModel) => ng.IPromise<common.models.Section<any>[]|boolean>;
        public deleteSection: (entity:mixins.SectionableModel, section:common.models.Section<any>) => ng.IPromise<boolean>;

        //TaggbleApiService
        public saveEntityTags: (entity:mixins.TaggableModel) => ng.IPromise<common.models.Tag[]|boolean>;

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q', '$location', '$state'];

        constructor(ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    paginationService:common.services.pagination.PaginationService,
                    $q:ng.IQService,
                    $location:ng.ILocationProvider,
                    $state:ng.ui.IState) {
            super(ngRestAdapter, paginationService, $q, $location, $state);
        }

        /**
         * Get an instance of the Article given data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        protected modelFactory(data:any, exists:boolean = false):common.models.Article {
            return new common.models.Article(data, exists);
        }

        /**
         * Get the api endpoint for the entity @todo declare with generic type that can be made specific in the implementation
         * @param entity
         * @returns {string}
         */
        public apiEndpoint(article?:common.models.Article):string {
            if(article){
                return '/articles/' + article.articleId;
            }
            return '/articles';
        }

        /**
         * Get a new article with no values and a set uuid
         * @returns {common.models.Article}
         */
        public newArticle(author:common.models.User):common.models.Article {

            return new common.models.Article({
                articleId: this.ngRestAdapter.uuid(),
                authorId: author.userId,
                _author: author
            });

        }

        /**
         * Returns the public facing URL for an article
         * @param article
         * @returns {string}
         */
        public getPublicUrl(article:common.models.Article):string {

            return this.getPublicUrlForEntity({permalink:article.getIdentifier()}, app.guest.articles.article.ArticleConfig.state);

        }

        /**
         * Save the article with all the nested entities too
         * @param article
         * @returns {IPromise<common.models.Article>}
         */
        public save(article:common.models.Article):ng.IPromise<common.models.Article> {

            return this.saveModel(article, this.apiEndpoint() + '/' + article.articleId)
                .then(() => this.$q.when([
                    this.saveRelatedEntities(article),
                    this.runQueuedSaveFunctions(),
                ]))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>article).resetChanged(); //reset so next save only saves the changed ones
                    article.setExists(true);
                    return article;
                });

        }

        /**
         * Save an article's comment
         * @param article
         * @param comment
         * @returns {IPromise<common.models.ArticleComment>}
         */
        public saveComment(article:common.models.Article, comment:common.models.ArticleComment):ng.IPromise<common.models.ArticleComment> {
            comment.createdAt = moment();

            return this.ngRestAdapter.post('/articles/' + article.articleId + '/comments', comment)
                .then(() => {
                    return comment;
                });
        }

        /**
         * Save all the related entities concurrently
         * @param article
         * @returns {IPromise<any[]>}
         */
        private saveRelatedEntities(article:common.models.Article):ng.IPromise<any> {

            return this.$q.all([ //save all related entities
                this.saveEntitySections(article),
                this.saveEntityTags(article),
                this.saveArticleMetas(article),
            ]);

        }

        /**
         * Save article metas
         * @param article
         * @returns {any}
         */
        private saveArticleMetas(article:common.models.Article):ng.IPromise<common.models.ArticleMeta[]|boolean> {
            if (article.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>article).getChanged(true);

                if (!_.has(changes, '_articleMetas')) {
                    return this.$q.when(false);
                }
            }

            // Remove the meta tags which have not been used
            let metaTags:common.models.ArticleMeta[] = _.filter(article._articleMetas, (metaTag) => {
                return !_.isEmpty(metaTag.metaContent);
            });

            return this.ngRestAdapter.put(`/articles/${article.articleId}/meta`, metaTags)
                .then(() => {
                    return article._articleMetas;
                });
        }

    }


    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



