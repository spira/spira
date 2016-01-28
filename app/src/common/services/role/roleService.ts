namespace common.services.role {

    export const namespace = 'common.services.role';

    export class RoleService extends AbstractApiService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q', '$location', '$state', 'ngJwtAuthService', '$mdDialog', 'regionService'];

        constructor(ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    paginationService:common.services.pagination.PaginationService,
                    $q:ng.IQService,
                    $location:ng.ILocationProvider,
                    $state:ng.ui.IState) {
            super(ngRestAdapter, paginationService, $q, $location, $state);
        }

        /**
         * Get an instance of the Article given data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        public modelFactory(data:any, exists:boolean = false):common.models.Role {
            return new common.models.Role(data, exists);
        }

        /**
         * Get the api endpoint for the model
         * @returns {string}
         */
        public apiEndpoint(role?:common.models.Role):string {
            if(role){
                return '/roles/' + role.getKey();
            }
            return '/roles';
        }

    }

    angular.module(namespace, [])
        .service('roleService', RoleService);

}



