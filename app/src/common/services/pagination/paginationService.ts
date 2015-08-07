module common.services.pagination {

    export const namespace = 'common.services.pagination';

    export class Paginator {

        private static defaultCount:number = 10;

        private count:number = Paginator.defaultCount;
        private currentIndex:number = 0;
        private modelFactory:common.models.IModelFactory;

        constructor(private url:string, private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

            this.modelFactory = (data:any) => data; //set a default factory that just returns the data

        }

        /**
         * Build the range header
         * @param from
         * @param to
         */
        private static getRangeHeader(from:number, to:number):string {
            return 'entities=' + from + '-' + to;
        }

        /**
         * Get the response from the collection endpoint
         * @param count
         * @param index
         */
        private getResponse(count:number, index:number = this.currentIndex):ng.IPromise<common.models.IModel[]> {

            return this.ngRestAdapter.get(this.url, {
                Range: Paginator.getRangeHeader(index, index + count - 1)
            }).then((response) => {
                return _.map(response.data, (modelData) => this.modelFactory(modelData));
            });

        }

        /**
         * Set the default count to get responses
         * @param count
         * @returns {common.services.pagination.Paginator}
         */
        public setCount(count:number):Paginator {
            this.count = count;
            return this;
        }

        /**
         * Get the next set of paginated results
         * @returns {IPromise<TResult>}
         * @param count
         */
        public getNext(count:number = this.count):ng.IPromise<any[]> {

            let responsePromise = this.getResponse(count);

            this.currentIndex += this.count;

            return responsePromise;
        }

        /**
         * Set the index back to 0 or specified index value
         */
        public reset(index:number = 0):Paginator {
            this.currentIndex = index;
            return this;
        }

        /**
         * Get the a specific range
         * @returns {ng.IPromise<any[]>}
         * @param first
         * @param last
         */
        public getRange(first:number, last:number):ng.IPromise<any[]> {

            return this.getResponse(last-first+1, first);
        }


        public setModelFactory(modelFactory:common.models.IModelFactory):Paginator{
            this.modelFactory = modelFactory;
            return this;
        }

    }

    export class PaginationService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        /**
         * Get an instance of the Paginator
         * @param url
         * @returns {common.services.pagination.Paginator}
         */
        public getPaginatorInstance(url:string):Paginator {
            return new Paginator(url, this.ngRestAdapter);
        }

    }

    angular.module(namespace, [])
        .service('paginationService', PaginationService);

}
