namespace common.services.user {

    export const namespace = 'common.services.user';

    export class UserService extends AbstractApiService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q', 'ngJwtAuthService', '$mdDialog', 'regionService'];

        constructor(ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    paginationService:common.services.pagination.PaginationService,
                    $q:ng.IQService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $mdDialog:ng.material.IDialogService,
                    private regionService:common.services.region.RegionService) {
            super(ngRestAdapter, paginationService, $q);
        }

        /**
         * Get an instance of the Article given data
         * @param data
         * @returns {common.models.Article}
         * @param exists
         */
        public modelFactory(data:any, exists:boolean = false):common.models.User {
            return new common.models.User(data, exists);
        }

        /**
         * Get the api endpoint for the model
         * @returns {string}
         */
        protected apiEndpoint():string {
            return '/users';
        }

        /**
         * Get the users paginator
         * @returns {Paginator}
         */
        public getUsersPaginator():common.services.pagination.Paginator {

            return this.getPaginator();
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

            return this.ngRestAdapter.put(this.apiEndpoint() + '/' + user.userId, user)
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
                .head(this.apiEndpoint() + '/email/' + email)
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
                .remove(`${this.apiEndpoint()}/${email}/password`);
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
                .patch(`${this.apiEndpoint()}/${user.userId}`, _.pick(user, 'emailConfirmed'), {'email-confirm-token':emailConfirmToken})
            ;
        }

        /**
         * Send request to update all user information
         * @param user
         * @returns {ng.IHttpPromise<any>}
         */
        public saveUser(user:common.models.User):ng.IPromise<common.models.User|boolean> {

            let changes:any = (<common.decorators.IChangeAwareDecorator>user).getChanged();

            if (_.isEmpty(changes)){
                return this.$q.when(false);
            }

            if (_.has(changes, 'regionCode')){
                this.regionService.setRegion(this.regionService.getRegionByCode(changes.regionCode));
            }

            return this.ngRestAdapter
                .patch(this.apiEndpoint()+'/' + user.userId, changes)
                .then(() => user);
        }

        /**
         * Save user with all related entities
         * @param user
         * @returns {IPromise<common.models.User>}
         */
        public saveUserWithRelated(user:common.models.User):ng.IPromise<common.models.User>{

            return this.saveUser(user)
                .then(() => this.saveRelatedEntities(user))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>user).resetChanged(); //reset so next save only saves the changed items
                    return user;
                });

        }

        /**
         * Save all related entities within user
         * @param user
         * @returns {IPromise<any[]>}
         */
        private saveRelatedEntities(user:common.models.User):ng.IPromise<any[]> {

            return this.$q.all([ //save all related entities
                this.saveUserProfile(user),
            ]);

        }

        /**
         * Save user profile
         * @param user
         * @returns {any}
         */
        private saveUserProfile(user:common.models.User):ng.IPromise<common.models.UserProfile|boolean>{

            if (!user._userProfile){ //don't try to save if there is no profile
                return this.$q.when(false);
            }

            let changes:any = (<common.decorators.IChangeAwareDecorator>user._userProfile).getChanged();
            if (_.isEmpty(changes)){
                return this.$q.when(false);
            }

            return this.ngRestAdapter.patch(`${this.apiEndpoint()}/${user.userId}/profile`, changes)
                .then(() => {
                    return user._userProfile;
                });

        }

        /**
         * Get full user information
         * @param user
         * @returns {ng.IPromise<common.models.User>}
         * @param withNested
         */
        public getUser(user:common.models.User, withNested:string[] = null):ng.IPromise<common.models.User> {

            return this.getModel(user.userId, withNested)
                .then((res) => this.modelFactory(res.data, true))

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



