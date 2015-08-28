namespace common.services.user {

    export const namespace = 'common.services.user';

    export class UserService {

        static $inject:string[] = ['ngRestAdapter', 'ngJwtAuthService', '$q', '$mdDialog', 'paginationService'];

        private cachedPaginator:common.services.pagination.Paginator;

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $q:ng.IQService,
                    private $mdDialog:ng.material.IDialogService,
                    private paginationService:common.services.pagination.PaginationService
        ) {

        }

        /**
         * Get an instance of a user from data
         * @param userData
         * @returns {common.models.User}
         * @param exists
         */
        public static userFactory(userData:global.IUserData, exists:boolean = false):common.models.User {
            return new common.models.User(userData, exists);
        }

        /**
         * Get the users paginator
         * @returns {Paginator}
         */
        public getUsersPaginator():common.services.pagination.Paginator {

            // Cache the paginator so subsequent requests can be collection length-aware
            if (!this.cachedPaginator){
                this.cachedPaginator = this.paginationService
                    .getPaginatorInstance('/users')
                    .setModelFactory(UserService.userFactory);
            }

            return this.cachedPaginator;
        }

        /**
         * Register a user
         * @param email
         * @param password
         * @param firstName
         * @param lastName
         * @returns {IPromise<global.IUserData>}
         */
        private register(email:string, password:string, firstName:string, lastName:string):ng.IPromise<global.IUserData> {

            let userData:global.IUserData = {
                userId: this.ngRestAdapter.uuid(),
                email: email,
                firstName: firstName,
                lastName: lastName,
                _userCredential: {
                    userCredentialId: this.ngRestAdapter.uuid(),
                    password: password,
                }
            };

            let user = new common.models.User(userData);

            return this.ngRestAdapter.put('/users/' + user.userId, user)
                .then(() => {
                    user.setExists(true);
                    return user;
                }); //return this user object
        }

        /**
         * Register and log in a user
         * @param email
         * @param password
         * @param firstName
         * @param lastName
         * @returns {IPromise<TResult>}
         */
        public registerAndLogin(email:string, password:string, firstName:string, lastName:string):ng.IPromise<any> {

            return this.register(email, password, firstName, lastName)
                .then((user:common.models.User) => {
                    return this.ngJwtAuthService.authenticateCredentials(user.email, user._userCredential.password);
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
                .head('/users/email/' + email)
                .then(() => true, () => false) //200 OK is true (email exists) 404 is false (email not registered)
            ;

        }

        /**
         * Brings up the reset password dialog
         */
        public promptResetPassword(email:string = undefined):void {
            this.$mdDialog.show({
                templateUrl: 'templates/app/guest/login/reset-password-dialog.tpl.html',
                controller: 'app.guest.resetPassword.controller',
                controllerAs: 'ResetPasswordController',
                clickOutsideToClose: true,
                locals: {
                    defaultEmail : email
                }
            });
        }

        /**
         * Reset a password for a user
         * @param email
         */
        public resetPassword(email:string):ng.IPromise<any> {
            return this.ngRestAdapter
                .skipInterceptor()
                .remove('/users/' + email + '/password');
        }

        /**
         * Confirm email update for a user
         * @param user
         * @param emailConfirmToken
         * @returns {ng.IHttpPromise<any>}
         */
        public confirmEmail(user:common.models.User, emailConfirmToken:string):ng.IPromise<any> {
            user.emailConfirmed = moment().toISOString();
            return this.ngRestAdapter
                .skipInterceptor((rejection:ng.IHttpPromiseCallbackArg<any>) => rejection.status == 422)
                .patch('/users/' + user.userId, _.pick(user, 'emailConfirmed'), {'email-confirm-token':emailConfirmToken})
            ;
        }

        /**
         * Send request to update all user information
         * @param user
         * @returns {ng.IHttpPromise<any>}
         */
        public updateUser(user:common.models.User):ng.IPromise<any> {
            return this.ngRestAdapter
                .patch('/users/' + user.userId, user.getAttributes(true));
        }

        /**
         * Get full user information
         * @param user
         * @returns {ng.IPromise<common.models.User>}
         */
        public getUser(user:common.models.User):ng.IPromise<common.models.User> {
            return this.ngRestAdapter.get('/users/' + user.userId, {
                    'With-Nested' : 'userCredential, userProfile, socialLogins'
                })
                .then((res) => {
                    return new common.models.User(res.data, true);
                });
        }

        /**
         * Get the auth user
         * @returns {common.models.User}
         */
        public getAuthUser():common.models.User {
            return <common.models.User>this.ngJwtAuthService.getUser();
        }

    }

    angular.module(namespace, [])
        .service('userService', UserService);

}



