namespace common.services.article {

    export const namespace = 'common.services.article';

    export class ArticleService extends PostService<common.models.Article> {

        /**
         * Get an instance of the given article data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        protected modelFactory(data:any, exists:boolean = false):common.models.Article {
            return new common.models.Article(data, exists);
        }

        /**
         * Get the api endpoint for the entity @todo declare with generic type that can be made specific in the implementation
         * @param article
         * @returns {string}
         */
        public apiEndpoint(article?:common.models.Article):string {
            if(article){
                return '/articles/' + article.postId;
            }
            return '/articles';
        }

        /**
         * Get a new article with no values and a set uuid
         * @returns {common.models.Article}
         */
        public newEntity(author:common.models.User):common.models.Article {

            return new common.models.Article({
                postId: this.ngRestAdapter.uuid(),
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

    }


    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



