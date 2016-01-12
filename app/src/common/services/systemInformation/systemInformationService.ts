namespace common.services.systemInformation {

    export const namespace = 'common.services.systemInformation';

    export interface ISystemInformationSources {
        app: common.models.SystemInformation;
        api: common.models.SystemInformation;
    }

    export class SystemInformationService {

        static $inject:string[] = ['ngRestAdapter', '$q'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private $q:ng.IQService) {

        }

        /**
         * Get an instance of the Article given data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        public modelFactory(data:any, exists:boolean = false):common.models.SystemInformation {
            return new common.models.SystemInformation(data, exists);
        }

        /**
         * Get all countries from the API
         * @returns {any}
         */
        public getSystemInformation():ng.IPromise<ISystemInformationSources> {

            let appPromise = this.ngRestAdapter.api('/').get('system-information.json')
                .then((res) => this.modelFactory(res.data))
                ;

            let apiPromise = this.ngRestAdapter.get('/utility/system-information')
                .then((res) => this.modelFactory(res.data))
                ;

            return this.$q.all({app: appPromise, api: apiPromise});
        }
    }

    angular.module(namespace, [])
        .service('systemInformationService', SystemInformationService);

}



