namespace common.models {

    @tracksChanges
    export class User extends Model implements global.IUserData {

        public userId:string;
        public email:string;
        public firstName:string;
        public lastName:string;
        public emailConfirmed:string;
        public country:string;
        public avatarImgUrl:string;
        public _userCredential:global.IUserCredential;
        public _userProfile:common.models.UserProfile;

        constructor(data:global.IUserData) {
            super(data);

            _.assign(this, data);
        }

        public fullName() {
            return this.firstName + ' ' + this.lastName;
        }

    }

}



