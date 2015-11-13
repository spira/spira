namespace common.services {

    export interface IExtendedApiService {
        save?(entity):ng.IPromise<any>;
        getPublicUrl?(entity):string;
    }

    export interface IQueuedSaveProcess {
        ():ng.IPromise<any>;
    }

    export abstract class AbstractApiService {

        protected queuedSaveProcessFunctions:IQueuedSaveProcess[] = [];
        protected cachedPaginator:common.services.pagination.Paginator;

        constructor(protected ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    protected paginationService:common.services.pagination.PaginationService,
                    protected $q:ng.IQService,
                    protected $location:ng.ILocationProvider,
                    protected $state:ng.ui.IState) {
        }

        protected abstract modelFactory(data:any, exists?:boolean):common.models.IModel;

        public abstract apiEndpoint(entity?:common.models.IModel):string;

        /**
         * Get the paginator
         * @returns {Paginator}
         */
        public getPaginator():common.services.pagination.Paginator {

            //cache the paginator so subsequent requests can be collection length-aware
            if (!this.cachedPaginator) {
                this.cachedPaginator = this.paginationService
                    .getPaginatorInstance(this.apiEndpoint())
                    .setModelFactory(this.modelFactory);
            }

            return this.cachedPaginator;
        }

        /**
         * Returns the public url for a given entity given it's (public) configuration state and params.
         * You should extend this function in your service.
         * @param params
         * @param state
         * @returns {string}
         */
        protected getPublicUrlForEntity(params:any, state:global.IState):string {

            // @Todo: Typings for $location and $state are not up to date
            return (<any>this.$location).protocol() + '://' + (<any>this.$location).host() + (<any>this.$state).href(state, params);

        }

        /**
         * Get model given an identifier (uuid or permalink)
         * @param identifier
         * @param withNested
         * @returns {ng.IPromise<T>}
         */
        public getModel<T extends common.models.AbstractModel>(identifier:string, withNested:string[] = null):ng.IPromise<T> {

            return this.ngRestAdapter.get(this.apiEndpoint() + '/' + identifier, {
                'With-Nested': () => {
                    if (!withNested) {
                        return null;
                    }
                    return withNested.join(', ');
                }
            }).then((res:ng.IHttpPromiseCallbackArg<T>) => this.modelFactory(res.data, true));
        }

    /**
     * Get all instances of a model
     * Usually a paginator should be used, but sometimes the entire dataset is required
     * @param withNested
     * @returns {ng.IPromise<T[]>}
     * @param endpoint
     */
        public getAllModels<T extends common.models.AbstractModel>(withNested:string[] = null, endpoint:string = this.apiEndpoint()):ng.IPromise<T[]> {

            return this.ngRestAdapter.get(endpoint, {
                'With-Nested': () => {
                    if (!withNested) {
                        return null;
                    }
                    return withNested.join(', ');
                }
            }).then((res:ng.IHttpPromiseCallbackArg<T[]>) => _.map(res.data, (modelData) => this.modelFactory(modelData, true)));
        }

        /**
         * Save model given the entity and endpoint
         * @todo swap params to follow common pattern of rest adapter
         * @param entity
         * @param endPoint
         * @returns {any}
         */
        public saveModel<T extends common.models.AbstractModel>(entity:T, endPoint:string):ng.IPromise<T|boolean> {
            let method = entity.exists() ? 'patch' : 'put';

            let saveData = entity.getAttributes();

            if (entity.exists()) {
                saveData = (<common.decorators.IChangeAwareDecorator>entity).getChanged();
            }

            if (_.size(saveData) == 0) { // If there is nothing to save, don't make an API call
                return this.$q.when(true);
            }

            return this.ngRestAdapter[method](endPoint, saveData)
                .then(() => entity);

        }

    /**
     * Get the nested attributes ready for save. Returns null if there is nothing to save
     * @param entity
     * @param nestedKey
     * @param getPartial
     * @returns {any}
     * @param filterExisting
     * @param alwayIncludeProperties
     */
        public getNestedCollectionRequestObject(entity:common.models.AbstractModel, nestedKey:string, getPartial:boolean = true, filterExisting:boolean = true, alwayIncludeProperties:string[] = null):Object[]{

            let nestedCollection:common.models.AbstractModel[] = _.get(entity, nestedKey, null);

            //if there is no nested attributes or it is an empty array, return
            if (!nestedCollection || _.isEmpty(nestedCollection)){
                return null;
            }

            if(entity.exists()){
                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                //if the entity does not have any changes registered for the attribute, exit
                if (!_.has(changes, nestedKey)) {
                    return null;
                }
            }

            let nestedResponseObjects = _.chain(<common.models.AbstractModel[]>nestedCollection)
                .filter((nestedModel:common.models.AbstractModel) => {
                    if (!filterExisting){
                        return true;
                    }
                    //filter out the existing models with no changes
                    return !(nestedModel.exists() && _.size((<common.decorators.IChangeAwareDecorator>nestedModel).getChanged(true)) === 0);
                })
                .map((nestedModel:common.models.AbstractModel) => {
                    if (getPartial && nestedModel.exists()){
                        //return the partial changes
                        return _.merge((<common.decorators.IChangeAwareDecorator>nestedModel).getChanged(true), _.pick(nestedModel, alwayIncludeProperties));
                    }
                    //return all the attributes
                    return nestedModel.getAttributes(true);
                })
                .value();

            //if after filtering there is no changes, exit.
            if (_.isEmpty(nestedResponseObjects)){
                return null;
            }
            return nestedResponseObjects;

        }

        /**
         * Run all queued save functions, returning promise of success
         * @returns {IPromise<TResult>}
         */
        protected runQueuedSaveFunctions():ng.IPromise<any> {

            let promises = _.map(this.getQueuedSaveProcessFunctions(), (queuedSaveFunction:IQueuedSaveProcess) => queuedSaveFunction());

            return this.$q.all(promises).then(() => {
                this.dumpQueueSaveFunctions();
            });
        }

        /**
         * Get all the queued save functions
         * @returns {IQueuedSaveProcess[]}
         */
        protected getQueuedSaveProcessFunctions():IQueuedSaveProcess[] {
            return this.queuedSaveProcessFunctions;
        }

        /**
         * Add a new queued save function
         * @param fn
         */
        public addQueuedSaveProcessFunction(fn:IQueuedSaveProcess):void {
            this.queuedSaveProcessFunctions.push(fn);
        }

        /**
         * Clear all queued save functions
         */
        public dumpQueueSaveFunctions():void {
            this.queuedSaveProcessFunctions = [];
        }
    }

}



