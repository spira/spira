module common.services {

    export const namespace = 'common.services';

    export class UserService {

        static $inject:string[] = ['ngRestAdapter', 'ngJwtAuthService', '$q'];
        constructor(
            private ngRestAdapter: NgRestAdapter.INgRestAdapterService,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private $q:ng.IQService) {

        }

        /**
         * Get all users from the API
         * @returns {any}
         */
        public getAllUsers(){

            return this.ngRestAdapter.get('/users')
                .then((res) => {
                    return res.data;
                })
            ;

        }

        /**
         * Register a user
         * @param user
         * @returns {ng.IHttpPromise<any>}
         */
        public register(user:global.IUser):ng.IPromise<global.IUser>{

            return this.ngRestAdapter.put('/users/'+user.userId, user);
        }

        /**
         * Register and log in a user
         * @param user
         * @returns {IPromise<TResult>}
         */
        public registerAndLogin(user:global.IUser):ng.IPromise<any>{

            return this.register(user)
                .then(() => {
                    return this.ngJwtAuthService.authenticateCredentials(user.email, user._credentials.password);
                })
            ;

        }

    }

    angular.module(namespace+'.userService', [])
        .service('userService', UserService);

}



