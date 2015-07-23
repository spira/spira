module common.services.user {

    export const namespace = 'common.services.user';

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
         * @param email
         * @param password
         * @param firstName
         * @param lastName
         * @returns {ng.IHttpPromise<any>}
         */
        private register(email:string, password:string, firstName:string, lastName:string):ng.IPromise<global.IUser>{

            let user:global.IUser = {
                userId: this.ngRestAdapter.uuid(),
                email: email,
                firstName: firstName,
                lastName: lastName,
                _credentials: {
                    userCredentialId: this.ngRestAdapter.uuid(),
                    password: password,
                }
            };

            return this.ngRestAdapter.put('/users/'+user.userId, user)
                .then(() => user); //return this user object
        }

        /**
         * Register and log in a user
         * @param email
         * @param password
         * @param firstName
         * @param lastName
         * @returns {IPromise<TResult>}
         */
        public registerAndLogin(email:string, password:string, firstName:string, lastName:string):ng.IPromise<any>{

            return this.register(email, password, firstName, lastName)
                .then((user) => {
                    return this.ngJwtAuthService.authenticateCredentials(user.email, user._credentials.password);
                })
            ;

        }

        /**
         * Check if an email has been registered
         * @param email
         * @returns {ng.IPromise<boolean>}
         */
        public isEmailRegistered(email:String):ng.IPromise<boolean> {

            return this.ngRestAdapter
                .skipInterceptor()
                .head('/users/email/'+email)
                .then(() => true, () => false) //200 OK is true (email exists) 404 is false (email not registered)
            ;

        }
    }

    angular.module(namespace, [])
        .service('userService', UserService);

}



