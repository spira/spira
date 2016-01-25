namespace common.models {

    @common.decorators.changeAware.changeAware
    export class User extends AbstractModel implements global.IUserData {

        protected __nestedEntityMap:INestedEntityMap = {
            _userProfile: UserProfile,
            _socialLogins: UserSocialLogin,
            _userCredential: UserCredential,
            _roles: Role,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __primaryKey = 'userId';

        public userId:string;
        public email:string;
        public username:string;
        public firstName:string;
        public lastName:string;
        public emailConfirmed:string;
        public country:string;
        public regionCode:string;
        public avatarImgUrl:string;
        public avatarImgId:string;
        public timezoneIdentifier:string;
        public _userCredential:UserCredential;
        public _userProfile:common.models.UserProfile;
        public _socialLogins:common.models.UserSocialLogin[] = [];
        public _roles:common.models.Role[] = [];
        public roles:string[] = []; //list of role keys, supplied in token
        public _uploadedAvatar:common.models.Image;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);

            if (this._roles && this._roles.length > 0){
                this.roles = _.pluck(this._roles, 'key');
            }
        }

        /**
         * Getter for the user's full name
         * @returns {string}
         */
        get fullName():string {
            return _.filter([this.firstName, this.lastName], _.identity).join(' ');
        }

        /**
         * Check if the user is an administrator
         * @returns {boolean}
         */
        public isAdmin():boolean {

            return _.contains(this.roles, common.models.Role.adminRoleKey);
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
         * Get comma separated display value for user's roles
         * @returns {any}
         */
        public rolesDisplay():string {

            return _.map(this.roles, (role:string) => {
                return _.capitalize(_.words(role).join(' '));
            }).join(', ');

        }

    }

}



