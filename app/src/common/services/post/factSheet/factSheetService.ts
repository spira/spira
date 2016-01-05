namespace common.services.factSheet {

    export const namespace = 'common.services.factSheet';

    export class FactSheetService extends PostService<common.models.FactSheet> {

        /**
         * Get an instance of the given fact sheet data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        protected modelFactory(data:any, exists:boolean = false):common.models.FactSheet {
            return new common.models.FactSheet(data, exists);
        }

        /**
         * Get the api endpoint for the entity
         * @param factSheet
         * @returns {string}
         */
        public apiEndpoint(factSheet?:common.models.FactSheet):string {
            if(factSheet){
                return '/fact-sheets/' + factSheet.postId;
            }
            return '/fact-sheets';
        }

        /**
         * Get a new fact sheet with no values and a set uuid
         * @returns {common.models.FactSheet}
         */
        public newEntity(author:common.models.User):common.models.FactSheet {

            return new common.models.FactSheet({
                postId: this.ngRestAdapter.uuid(),
                authorId: author.userId,
                _author: author
            });

        }

        /**
         * Returns the public facing URL for an article
         * @param factSheet
         * @returns {string}
         */
        public getPublicUrl(factSheet:common.models.FactSheet):string {

            return 'not yet implemented';

            //return this.getPublicUrlForEntity({permalink:factSheet.getIdentifier()}, app.guest.articles.article.ArticleConfig.state);

        }

    }


    angular.module(namespace, [])
        .service('factSheetService', FactSheetService);

}



