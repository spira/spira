namespace common.services.pagination {

    export const namespace = 'common.services.pagination';

    export class PaginatorException extends global.SpiraException {}

    export interface IRangeHeaderData{
        entityName:string;
        from:number;
        to:number;
        count:number|string;
    }

    export interface ICachedRequest {
        [key:string] : ng.IPromise<any>
    }

    export class Paginator {

        private static defaultCount:number = 10;

        private count:number = Paginator.defaultCount;
        private currentIndex:number = 0;
        private modelFactory:common.models.IModelFactory;
        private queryString:string = '';
        private withNested:string = null;
        private similarTo:string = '';
        private doCache:boolean = false;

        private resolveNoResultsValue:any = [];
        private resolveNoResults:boolean = false;

        public entityCountTotal:number;


        constructor(private url:string,
                    private paginationServiceInstance:PaginationService,
                    private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private $q:ng.IQService,
                    private $window:ng.IWindowService) {

            this.modelFactory = (data:any, exists:boolean) => data; //set a default factory that just returns the data

        }

        /**
         * Set child entities to retrieve with entity.
         * @param nestedEntities
         * @returns {common.services.pagination.Paginator}
         */
        public setNested(nestedEntities:string[]):Paginator {

            if(!_.isNull(nestedEntities)) {
                this.withNested = nestedEntities.join(', ');
            }
            return this;

        }

        /**
         * Turn on (or off) request caching
         * @param doCache
         */
        public cacheRequests(doCache:boolean = true):Paginator{
            this.doCache = doCache;
            return this;
        }

        /**
         * Clear the cache
         */
        public bustCache(){
            this.paginationServiceInstance.bustCache();
            return this;
        }

        /**
         * Method to test when to skip the interceptor
         * @param rejection
         * @returns {boolean}
         */
        private static conditionalSkipInterceptor(rejection: ng.IHttpPromiseCallbackArg<any>): boolean {

            return _.contains([416, 404], rejection.status);
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
         * scenario
         * 34 entities
         * request 30 - 34
         * count 5
         */

        /**
         * Get the response from the collection endpoint
         * @param count
         * @param index
         */
        private getResponse(count:number, index:number = this.currentIndex):ng.IPromise<common.models.IModel[]> {

            if (this.entityCountTotal && index >= this.entityCountTotal){
                return this.$q.reject(new PaginatorException("No more results found!"));
            }

            let last = index + count - 1;
            if (this.entityCountTotal && last >= this.entityCountTotal){
                last = this.entityCountTotal - 1;
            }

            let url = this.url;
            if(!_.isEmpty(this.queryString)) {
                url += '?q=' + btoa(this.queryString);
            }
            if(!_.isEmpty(this.similarTo)) {
                url += '/' + this.similarTo + '/similar';
            }

            let header = {
                Range: Paginator.getRangeHeader(index, last)
            };

            if(!_.isNull(this.withNested)) {
                header['With-Nested'] = this.withNested;
            }

            return this.doRequest(url, header).then((response:ng.IHttpPromiseCallbackArg<any>) => {
                this.processContentRangeHeader(response.headers);
                return _.map(response.data, (modelData) => this.modelFactory(modelData, true));
            }).catch((response:ng.IHttpPromiseCallbackArg<any>) => {

                let errorMessage;
                if(response.status == 404){ //no content
                    this.entityCountTotal = 0;
                    errorMessage = this.$q.reject(new PaginatorException("Search returned no results!"));
                }else{
                    errorMessage = "No more results found!";
                }

                if (this.resolveNoResults){
                    return this.resolveNoResultsValue;
                }

                return this.$q.reject(new PaginatorException(errorMessage));
            });

        }

        public doRequest(url, header):ng.IPromise<any> {

            let requestHash = url + ':' + angular.toJson(header);

            if (this.doCache && _.has(this.paginationServiceInstance.cachedRequests, requestHash)){
                return this.paginationServiceInstance.cachedRequests[requestHash];
            }

            let request = this.ngRestAdapter
                .skipInterceptor(Paginator.conditionalSkipInterceptor)
                .get(url, header);

            if (this.doCache){
                this.paginationServiceInstance.cachedRequests[requestHash] = request;
            }

            return request;
        }

        /**
         * Return an array of numbers which indicates how many pages of results there are.
         * @returns {number[]}
         */
        public getPages():number[] {

            return _.range(1, Math.ceil(this.entityCountTotal/this.getCount()) + 1);

        }

        /**
         * Set the index back to 0 and get a response from the collection endpoint with added query param. If an empty
         * string is passed through the results are not filtered.
         * @param query
         * @returns {IPromise<TResult>}
         */
        public query(query:string):ng.IPromise<any[]> {

            this.reset();

            this.queryString = query;

            return this.getResponse(this.count);

        }

        /**
         * Set the index back to 0 and get a response from the collection endpoint with added complex query param. If an empty
         * string is passed through the results are not filtered.
         * @param query
         * @returns {ng.IPromise<common.models.IModel[]>}
         */
        public complexQuery(query:any):ng.IPromise<any[]> {

            this.reset();

            this.queryString = angular.toJson(_.cloneDeep(query));

            return this.getResponse(this.count);

        }

        /**
         * Get a collection of similar entities to a given entity.
         * @param identifier
         * @returns {ng.IPromise<common.models.IModel[]>}
         */
        public getSimilar(identifier:string):ng.IPromise<any[]> {

            this.reset();

            this.similarTo = identifier;

            return this.getResponse(this.count);

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
         * Get the current count
         * @returns {number}
         */
        public getCount():number {
            return this.count;
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
         * Get results with traditional pagination page numbers (1 - indexed)
         * @param page
         */
        public getPage(page:number):ng.IPromise<any[]> {

            let first = this.count * (page - 1);

            let responsePromise = this.getResponse(this.count, first);

            this.currentIndex = first;

            return responsePromise;

        }

        /**
         * Set the index back to 0 or specified index value
         */
        public reset(index:number = 0):Paginator {
            this.currentIndex = index;
            this.queryString = '';
            this.similarTo = '';
            this.entityCountTotal = null;

            return this;
        }

        /**
         * Set no result promise resolution
         * @param resolveNoResults
         * @param resolveNoResultsValue
         */
        public noResultsResolve(resolveNoResults = true, resolveNoResultsValue:any = []):Paginator{
            this.resolveNoResults = resolveNoResults;
            this.resolveNoResultsValue = resolveNoResultsValue;
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

        private processContentRangeHeader(headers:ng.IHttpHeadersGetter):void {
            let headerString = headers('Content-Range');

            if (!headerString){
                return;
            }

            let headerParts = Paginator.parseContentRangeHeader(headerString);

            if (_.isNumber(headerParts.count)){
                this.entityCountTotal = <number>headerParts.count;
            }
        }

        public static parseContentRangeHeader(headerString:String):IRangeHeaderData {
            let parts = headerString.split(/[\s\/]/);

            if (parts.length !== 3){
                throw new PaginatorException("Invalid range header; expected pattern: `entities 1-10/50`, got `"+headerString+"`");
            }

            let rangeParts = parts[1].split('-');

            if (rangeParts.length !== 2){
                throw new PaginatorException("Invalid range header; expected pattern: `entities 1-10/50`, got `"+headerString+"`");
            }

            let count:any = parts[2];
            if (!_.isNaN(Number(count))){
                count = parseInt(count);
            }

            return {
                entityName: parts[0],
                from: parseInt(rangeParts[0]),
                to: parseInt(rangeParts[1]),
                count: count,
            }

        }
    }

    export class PaginationService {


        public cachedRequests:ICachedRequest = {};

        static $inject:string[] = ['ngRestAdapter', '$q', '$window'];
        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private $q:ng.IQService,
                    private $window:ng.IWindowService) {

        }

        /**
         * Get an instance of the Paginator
         * @param url
         * @returns {common.services.pagination.Paginator}
         */
        public getPaginatorInstance(url:string):Paginator {
            return new Paginator(url, this, this.ngRestAdapter, this.$q, this.$window);
        }

        /**
         * Clear the cache
         * @returns {common.services.pagination.PaginationService}
         */
        public bustCache(){
            this.cachedRequests = {};
            return this;
        }

    }

    angular.module(namespace, [])
        .service('paginationService', PaginationService);

}