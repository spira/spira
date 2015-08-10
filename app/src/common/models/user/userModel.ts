namespace common.models {

    @changeAware
    export class User extends Model implements global.IUserData {

        public static adminType = 'admin';
        public static guestType = 'guest';
        public static userTypes:string[] = [User.adminType, User.guestType];

        public userId:string;
        public email:string;
        public firstName:string;
        public lastName:string;
        public emailConfirmed:string;
        public country:string;
        public avatarImgUrl:string;
        public timezoneIdentifier:string;
        public _userCredential:global.IUserCredential;
        public _userProfile:common.models.UserProfile;
        public userType:string;

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

    }

}



