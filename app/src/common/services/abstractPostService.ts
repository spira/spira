namespace common.services {

    export abstract class PostService<M extends common.models.Post> extends AbstractApiService implements common.services.IExtendedApiService, common.mixins.SectionableApiService, common.mixins.TaggableApiService, common.mixins.LocalizableApiService, common.mixins.MetaableApiService {

        //SectionableApiService
        public saveEntitySections: (entity:mixins.SectionableModel) => ng.IPromise<common.models.Section<any>[]|boolean>;
        public deleteSection: (entity:mixins.SectionableModel, section:common.models.Section<any>) => ng.IPromise<boolean>;
        public saveEntitySectionLocalizations: (entity:mixins.SectionableModel) => ng.IPromise<any>;
        public newSection:<S extends common.models.AbstractModel>(sectionType:string, content:S) => common.models.Section<S>;

        //TaggbleApiService
        public saveEntityTags: (entity:mixins.TaggableModel) => ng.IPromise<common.models.Tag[]|boolean>;

        //LocalizableApiService
        public saveEntityLocalizations: (entity:mixins.LocalizableModel) => ng.IPromise<common.models.Localization<any>[]|boolean>;

        //MetaableApiService
        public hydrateMetaCollection: (entity:models.IMetaableModel) => common.models.Meta[];

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q', '$location', '$state'];

        constructor(ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    paginationService:common.services.pagination.PaginationService,
                    $q:ng.IQService,
                    $location:ng.ILocationProvider,
                    $state:ng.ui.IState) {
            super(ngRestAdapter, paginationService, $q, $location, $state);
        }

        public abstract newEntity(author:common.models.User):M;

        /**
         * Save with all the nested entities too
         * @param entity
         * @returns {IPromise<M>}
         */
        public save(entity:M):ng.IPromise<M> {

            return this.saveModel(entity)
                .then(() => this.$q.when([
                    this.saveRelatedEntities(entity),
                    this.runQueuedSaveFunctions(),
                ]))
                .then(() => {
                    (<common.decorators.changeAware.IChangeAwareDecorator>entity).resetChanged(); //reset so next save only saves the changed ones
                    entity.setExists(true);
                    return entity;
                });

        }

        /**
         * Save a comment to this entity
         * @param entity
         * @param comment
         * @returns {IPromise<common.models.Comment>}
         */
        public saveComment(entity:M, comment:common.models.Comment):ng.IPromise<common.models.Comment> {
            comment.createdAt = moment();

            return this.ngRestAdapter.post(this.apiEndpoint(entity) + '/comments', comment)
                .then(() => {
                    return comment;
                });
        }

        /**
         * Save all the related entities concurrently
         * @param entity
         * @returns {IPromise<any[]>}
         */
        private saveRelatedEntities(entity:M):ng.IPromise<any> {

            return this.$q.all([ //save all related entities
                this.saveEntitySections(entity),
                this.saveEntityTags(entity),
                this.saveEntityLocalizations(entity),
                this.saveEntityMetas(entity),
            ]);

        }

        /**
         * Save entity metas
         * @param entity
         * @returns {any}
         */
        private saveEntityMetas(entity:M):ng.IPromise<common.models.Meta[]|boolean> {

            let requestObject = this.getNestedCollectionRequestObject(entity, '_metas', false);

            requestObject = _.filter(<Array<any>>requestObject, (metaTag) => {
                return !_.isEmpty(metaTag.metaContent);
            });

            if (!requestObject || _.isEmpty(requestObject)){
                return this.$q.when(false);
            }

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/meta', requestObject)
                .then(() => {
                    _.invoke(entity._metas, 'setExists', true);
                    return entity._metas;
                });
        }

    }

}



