module common.services.pagination {

    export const namespace = 'common.services.pagination';

    export class Paginator {

        private limit:number = 10;
        private skip:number = 0;

        constructor(private url:string, private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        private static getRangeHeader(from:number, to:number):string{
            return 'entities=' + from + '-' + to;
        }

        private getResponse(skip:number = this.skip, limit:number = this.limit):ng.IHttpPromise<any[]>{

            return this.ngRestAdapter.get(this.url, {
                Range: Paginator.getRangeHeader(skip, skip + limit - 1)
            });

        }

        /**
         * Get the next set of paginated results
         * @returns {ng.IPromise<any[]>}
         */
        public getNext():ng.IPromise<any[]> {

            let responsePromise =  this.getResponse();

            responsePromise.finally(() => {
                this.skip += this.limit;
            });

            return responsePromise.then((response) => {
                return response.data;
            });

        }




    }

    export class PaginationService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        public getPaginatorInstance(url:string){
            return new Paginator(url, this.ngRestAdapter);
        }

    }

    angular.module(namespace, [])
        .service('paginationService', PaginationService);

}

