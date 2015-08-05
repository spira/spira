module common.services.pagination {

    export const namespace = 'common.services.pagination';

    export class Paginator {

        private static defaultCount:number = 10;

        private count:number = Paginator.defaultCount;
        private currentIndex:number = 0;

        constructor(private url:string, private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {
        }

        private static getRangeHeader(from:number, to:number):string {
            return 'entities=' + from + '-' + to;
        }

        private getResponse(count:number = this.count, index:number = this.currentIndex):ng.IPromise<any[]> {

            return this.ngRestAdapter.get(this.url, {
                Range: Paginator.getRangeHeader(index, index + count - 1)
            }).then((response) => {
                return response.data;
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
        public getNext(count:number = this.currentIndex):ng.IPromise<any[]> {

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
         * @param count
         * @param index
         * @returns {ng.IPromise<any[]>}
         */
        public getRange(count:number = this.count, index:number = this.currentIndex):ng.IPromise<any[]> {

            return this.getResponse(count, index);
        }

    }

    export class PaginationService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        public getPaginatorInstance(url:string) {
            return new Paginator(url, this.ngRestAdapter);
        }

    }

    angular.module(namespace, [])
        .service('paginationService', PaginationService);

}

