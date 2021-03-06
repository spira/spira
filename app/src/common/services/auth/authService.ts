namespace common.services.auth {

    export const namespace = 'common.services.auth';

    export interface IImpersonationObject {
        originalUser: common.models.User;
        originalUserToken: string;
        impersonatedUser: common.models.User;
    }

    export class AuthService {

        public static impersonationStorageKey = 'impersonation';
        public impersonation:IImpersonationObject = null;
        public initialisedPromise:ng.IPromise<any>;

        static $inject:string[] = ['ngJwtAuthService', '$q', '$location', '$timeout', '$mdDialog', '$state', 'notificationService', '$window', 'ngRestAdapter'];

        constructor(private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $q:ng.IQService,
                    private $location:ng.ILocationService,
                    private $timeout:ng.ITimeoutService,
                    private $mdDialog:ng.material.IDialogService,
                    private $state:ng.ui.IStateService,
                    private notificationService:common.services.notification.NotificationService,
                    private $window:ng.IWindowService,
                    private ngRestAdapter:NgRestAdapter.INgRestAdapterService
        ) {

            this.loadStoredImpersonation();

            this.initialisedPromise = this.initialiseJwtAuthService().finally(() => {

                return this.$q.all([
                    this.processQueryToken(),
                    this.processLoginToken()
                ]);

            }).catch((e) => {
                console.error("Auth Initialisation failed: ", e);
            });

        }

        public loadStoredImpersonation() {
            let storedImpersonation:IImpersonationObject = angular.fromJson(this.$window.localStorage.getItem(AuthService.impersonationStorageKey));

            if (storedImpersonation) {
                storedImpersonation.originalUser = new common.models.User(storedImpersonation.originalUser);
                storedImpersonation.impersonatedUser = new common.models.User(storedImpersonation.impersonatedUser);
                this.impersonation = storedImpersonation;
            }
        }

        /**
         * Initialise the NgJwtAuthService
         * @returns {ng.IPromise<any>}
         */
        private initialiseJwtAuthService() {

            return this.ngJwtAuthService
                .registerUserFactory((subClaim: string, tokenData: global.JwtAuthClaims): ng.IPromise<common.models.User> => {
                    return this.$q.when(new common.models.User(tokenData._user, true));
                })
                .registerLoginPromptFactory((deferredCredentials:ng.IDeferred<NgJwtAuth.ICredentials>, loginSuccessPromise:ng.IPromise<NgJwtAuth.IUser>, currentUser:NgJwtAuth.IUser): ng.IPromise<any> => {

                    let dialogConfig:ng.material.IDialogOptions = {
                        templateUrl: 'templates/app/guest/login/login-dialog.tpl.html',
                        controller: 'app.guest.login.controller',
                        controllerAs: 'LoginController',
                        clickOutsideToClose: true,
                        locals : {
                            deferredCredentials: deferredCredentials,
                            loginSuccess: {
                                promise: loginSuccessPromise //nest the promise in a function as otherwise material will try to wait for it to resolve
                            },
                        }
                    };

                    return this.$timeout(_.noop) //first do an empty timeout to allow the controllers to init if login prompt is fired from within a .run() phase
                        .then(() => this.$mdDialog.show(dialogConfig));

                })
                .init() //initialise the auth service (kicks off the timers etc)
                .catch((err) => {
                    if (err === false){ //if the error was user failed to authenticate | @todo make the auth service throw a better error
                        return true;
                    }
                    return err;
                });

        }

        /**
         * Login using a social network
         * @param type
         * @param redirectState
         * @param redirectStateParams
         */
        public socialLogin(type:string, redirectState:string = this.$state.current.name, redirectStateParams:Object = this.$state.current.params):void {

            let url = '/auth/social/' + type;

            url += '?returnUrl=' + (<any>this.$window).encodeURIComponent(this.$state.href(redirectState, redirectStateParams));

            this.$window.location.href = url;

        }

        /**
         * Unlink a social login from a user
         * @param user
         * @param provider
         * @returns {ng.IHttpPromise<any>}
         */
        public unlinkSocialLogin(user:common.models.User, provider:string):ng.IPromise<any> {
            return this.ngRestAdapter
                .remove('/users/' + user.userId + '/socialLogin/' + provider);
        }

        /**
         * Check the address bar for a new jwt token to process
         * @returns {any}
         */
        private processQueryToken():ng.IPromise<any> {

            this.removeFacebookHash();
            let queryParams = this.$location.search();
            if (queryParams.jwtAuthToken) {

                let queryTokenPromise = this.ngJwtAuthService.processNewToken(queryParams.jwtAuthToken);

                this.$location.search('jwtAuthToken', null);

                return queryTokenPromise;
            }

            return this.$q.when(true); //immediately resolve

        }

        /**
         * Removes the facebook return hash `#_=_`
         */
        private removeFacebookHash():void {

            if (this.$location.hash() == '_=_'){
                this.$location.hash('');
            }

        }

        /**
         * Check the url for password reset token and process it
         * @returns {any}
         */
        private processLoginToken():ng.IPromise<any> {

            let queryParams = this.$location.search();
            if (_.isEmpty(queryParams.loginToken)) {
                return this.$q.when(true); //immediately resolve
            }

            let token = queryParams.loginToken;

            /**
             * We do not remove the loginToken from the URL params at this point because that would cause a state
             * reload causing whichever state we're navigating to to fully load twice (all resolves are called again);
             * this results in unneeded XHRs. The loginToken is safely removed in the constructor of
             * ProfileController, this means that the state we navigate to when we use the loginToken will always be
             * profile. See profile.ts.
             */

            return this.ngJwtAuthService.exchangeToken(token)
                .catch((err) => {
                    this.notificationService.toast('Sorry, you have already tried to log in using this link').options({position:'top right'}).pop();
                });
        }

        public impersonateUser(user:common.models.User):ng.IPromise<common.models.User> {

            let userIdentifier = user.userId;
            let currentUser = this.ngJwtAuthService.getUser();

            this.impersonation = {
                originalUser : <common.models.User>currentUser,
                originalUserToken : this.ngJwtAuthService.rawToken,
                impersonatedUser: user
            };

            this.$window.localStorage.setItem(AuthService.impersonationStorageKey, angular.toJson(this.impersonation));

            return this.ngJwtAuthService.loginAsUser(userIdentifier);
        }

        public restoreFromImpersonation():ng.IPromise<any>{

            if (!this.impersonation){
                return this.$q.reject("No stashed token to restore");
            }

            return this.ngJwtAuthService.processNewToken(this.impersonation.originalUserToken).then(() => {
                this.impersonation = null;
                this.$window.localStorage.removeItem(AuthService.impersonationStorageKey);
                this.$state.reload();
                return this.ngJwtAuthService.refreshToken();
            });
        }

    }

    angular.module(namespace, [])
        .service('authService', AuthService);

}



