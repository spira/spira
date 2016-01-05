namespace app.abstract.profile {

    export const namespace = 'app.abstract.profile';

    export class AbstractProfileController {

        static $inject = [
            'userService',
            'notificationService',
            'authService',
            'countries',
            'timezones',
            'genderOptions',
            'regions',
            'providerTypes',
            'fullUserInfo',
        ];

        protected showEditPassword:boolean = false;

        constructor(
            private userService:common.services.user.UserService,
            private notificationService:common.services.notification.NotificationService,
            protected authService:common.services.auth.AuthService,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:common.services.timezones.ITimezoneDefinition,
            public genderOptions:common.models.IGenderOption[],
            private regions:global.ISupportedRegion[],
            public providerTypes:string[],
            public fullUserInfo:common.models.User
        ) {

            //if the user doesn't have a profile yet, create one
            if (!fullUserInfo._userProfile){
                fullUserInfo._userProfile = new common.models.UserProfile({
                    userId: fullUserInfo.userId,
                });
            }

        }

        public showEditCredential(){

            if(!this.fullUserInfo._userCredential){
                this.fullUserInfo._userCredential = new common.models.UserCredential({
                    userId: this.fullUserInfo.userId,
                });
            }

            this.showEditPassword = true;
        }

        /**
         * Edit profile form submit function
         * @returns {ng.IPromise<any>}
         */
        public updateUser():ng.IPromise<any> {

            return this.userService.saveUserWithRelated(this.fullUserInfo)
                .then(() => {
                    this.notificationService.toast('Profile update was successful').pop();
                    this.showEditPassword = false;
                },
                (err) => {
                    this.notificationService.toast('Profile update was unsuccessful, please try again').pop();
                })
        }

        /**
         * Register social login function for Profile Controller
         * @param type
         */
        public socialLogin(type:string):void {

            this.authService.socialLogin(type);

        }

        /**
         * Register unlink social login function for Profile Controller
         * @param type
         */
        public unlinkSocialLogin(type:string):void {

            this.authService.unlinkSocialLogin(this.fullUserInfo, type)
                .then(() => {

                    _.remove(this.fullUserInfo._socialLogins, {
                        'provider' : type
                    });

                    this.notificationService.toast('Your ' + _.capitalize(type) + ' has been unlinked from your account').pop();
                });

        }
    }
}




