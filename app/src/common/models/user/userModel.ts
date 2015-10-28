namespace common.models {

    @common.decorators.changeAware
    export class User extends AbstractModel implements global.IUserData {

        protected __nestedEntityMap:INestedEntityMap = {
            _userProfile: UserProfile,
            _socialLogins: UserSocialLogin,
            _userCredential: UserCredential,
            _roles: RoleAssignment,
        };

        public userId:string = undefined;
        public email:string = undefined;
        public firstName:string = undefined;
        public lastName:string = undefined;
        public emailConfirmed:string = undefined;
        public country:string = undefined;
        public regionCode:string = undefined;
        public avatarImgUrl:string = undefined;
        public avatarImgId:string = undefined;
        public timezoneIdentifier:string = undefined;
        public _userCredential:global.IUserCredential = undefined;
        public _userProfile:common.models.UserProfile = undefined;
        public _socialLogins:common.models.UserSocialLogin[] = undefined;
        public _roles:common.models.RoleAssignment[] = undefined;
        public _uploadedAvatar:common.models.Image = undefined;

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

            return _.any(this._roles, {roleKey: common.models.RoleAssignment.adminRoleKey});
        }

        /**
         * Checks to see if the user has a social login
         * @returns {boolean}
         */
        public hasSocialLogin(provider:string):boolean {
            // Typings for lodash must not have this callback shorthand
            return (<any>_).some(this._socialLogins, 'provider', provider);
        }

    }

}



