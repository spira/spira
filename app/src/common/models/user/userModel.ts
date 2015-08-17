namespace common.models {

    @common.decorators.changeAware
    export class User extends AbstractModel implements global.IUserData {

        public static adminType = 'admin';
        public static guestType = 'guest';
        public static userTypes:string[] = [User.adminType, User.guestType];

        public userId:string = undefined;
        public email:string = undefined;
        public firstName:string = undefined;
        public lastName:string = undefined;
        public emailConfirmed:string = undefined;
        public country:string = undefined;
        public avatarImgUrl:string = undefined;
        public timezoneIdentifier:string = undefined;
        public _userCredential:global.IUserCredential = undefined;
        public _userProfile:common.models.UserProfile = undefined;
        public _socialLogins:common.models.UserSocialLogin[] = undefined;
        public userType:string = undefined;

        constructor(data:global.IUserData) {
            super(data);

            _.assign(this, data);
        }

        /**
         * Get the user's full name
         * @returns {string}
         */
        public fullName():string {
            return this.firstName + ' ' + this.lastName;
        }

        /**
         * Check if the user is an administrator
         * @returns {boolean}
         */
        public isAdmin():boolean {
            return this.userType == User.adminType;
        }


        /**
         * Checks to see if the user has Facebook login
         * @returns {boolean}
         */
        public hasFacebookLogin():boolean {
            // Typings for lodash must not have this callback shorthand
            return (<any>_).some(this._socialLogins, 'provider', common.models.UserSocialLogin.facebookType);
        }

        /**
         * Checks to see if the user has Google login
         * @returns {boolean}
         */
        public hasGoogleLogin():boolean {
            // Typings for lodash must not have this callback shorthand
            return (<any>_).some(this._socialLogins, 'provider', common.models.UserSocialLogin.googleType);
        }
    }

}



