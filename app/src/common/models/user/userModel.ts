namespace common.models {

    export class ContactChip {
        public userId:string = undefined;
        public name:string = undefined; // firstName + lastName
        public avatarImgUrl:string = undefined;
        public email:string = undefined;

        constructor(data:any) {
            _.assign(this, data);
        }
    }

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

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
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
         * Checks to see if the user has a social login
         * @returns {boolean}
         */
        public hasSocialLogin(provider:string):boolean {
            // Typings for lodash must not have this callback shorthand
            return (<any>_).some(this._socialLogins, 'provider', provider);
        }

        /**
         * Returns the user in a form which is able to be used by Angular Material's md-contact-chips
         * @returns {common.models.ContactChip}
         */
        public contactChip():common.models.ContactChip {
            return new ContactChip({
                userId: this.userId,
                name: this.fullName(),
                avatarImgUrl: this.avatarImgUrl,
                email: this.email
            });
        }
    }

}



