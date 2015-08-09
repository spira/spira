namespace common.services.auth {

    export const namespace = 'common.services.auth';

    export class AuthService {

        public initialisedPromise:ng.IPromise<any>;

        static $inject:string[] = ['ngJwtAuthService', '$q', '$location', '$timeout', '$mdDialog', '$state', '$mdToast'];

        constructor(private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $q:ng.IQService,
                    private $location:ng.ILocationService,
                    private $timeout:ng.ITimeoutService,
                    private $mdDialog:ng.material.IDialogService,
                    private $state:ng.ui.IStateService,
                    private $mdToast:ng.material.IToastService
        ) {

            this.initialisedPromise = this.initialiseJwtAuthService().finally(() => {

                return this.$q.all([
                    this.processQueryToken(),
                    this.processPasswordResetToken()
                ]);

            }).catch((e) => {
                console.error("Auth Initialisation failed: ", e);
            });

        }

        /**
         * Initialise the NgJwtAuthService
         * @returns {ng.IPromise<any>}
         */
        private initialiseJwtAuthService() {

            return this.ngJwtAuthService
                .registerUserFactory((subClaim: string, tokenData: global.JwtAuthClaims): ng.IPromise<common.models.User> => {
                    return this.$q.when(new common.models.User(tokenData._user));
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
                .init(); //initialise the auth service (kicks off the timers etc)

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
        private processPasswordResetToken():ng.IPromise<any> {

            let queryParams = this.$location.search();
            if (_.isEmpty(queryParams.passwordResetToken)) {
                return this.$q.when(true); //immediately resolve
            }

            let token = queryParams.passwordResetToken;
            this.$location.search('passwordResetToken', null);

            return this.ngJwtAuthService.exchangeToken(token)
                .catch((err) => {
                    this.$mdToast.show(
                        this.$mdToast.simple()
                            .hideDelay(2000)
                            .position('top right')
                            .content("Sorry, you have already tried to reset your password using this link")
                    );
                });
        }

    }

    angular.module(namespace, [])
        .service('authService', AuthService);

}



