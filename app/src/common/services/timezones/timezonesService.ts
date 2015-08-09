namespace common.services.timezones {

    export const namespace = 'common.services.timezones';

    export interface ITimezoneDefinition {
        timezoneIdentifier:string;
        offset:number;
        isDst:boolean;
        displayOffset:string;
    }

    export class TimezonesService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        private timezonesCachePromise:ng.IPromise<ITimezoneDefinition[]> = null;

        /**
         * Get all timezones from the API
         * @returns {any}
         */
        public getAllTimezones():ng.IPromise<ITimezoneDefinition[]> {

            //store the promise in cache, so next time it is called the countries are resolved immediately.
            if (!this.timezonesCachePromise) {
                this.timezonesCachePromise = this.ngRestAdapter.get('/timezones')
                    .then((res) => {
                        return res.data;
                    })
                ;
            }

            return this.timezonesCachePromise;
        }

    }

    angular.module(namespace, [])
        .service('timezonesService', TimezonesService);

}



