namespace common.services {

    export abstract class AbstractApiService {


        protected cachedPaginator:common.services.pagination.Paginator;

        constructor(protected ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    protected paginationService:common.services.pagination.PaginationService,
                    protected $q:ng.IQService) {
        }


        protected abstract modelFactory(data:any, exists?:boolean):common.models.IModel;
        protected abstract apiEndpoint():string;

        /**
         * Get the paginator
         * @returns {Paginator}
         */
        public getPaginator():common.services.pagination.Paginator {

            //cache the paginator so subsequent requests can be collection length-aware
            if (!this.cachedPaginator){
                this.cachedPaginator = this.paginationService
                    .getPaginatorInstance(this.apiEndpoint())
                    .setModelFactory(this.modelFactory);
            }

            return this.cachedPaginator;
        }

        /**
         * Get model response given an identifier (uuid or permalink)
         * @param identifier
         * @param withNested
         * @returns {ng.IHttpPromise<any>}
         */
        protected getModel(identifier:string, withNested:string[] = null):ng.IPromise<ng.IHttpPromiseCallbackArg<any>> {

            return this.ngRestAdapter.get(this.apiEndpoint()+'/'+identifier, {
                'With-Nested' : () => {
                    if (!withNested){
                        return null;
                    }
                    return withNested.join(', ');
                }
            });
        }


    }
}


