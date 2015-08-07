module common.models {

    export class User implements global.IUserData, IModel{

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
            _.assign(this, data);

        }

        public fullName() {
            return this.firstName + ' ' + this.lastName;
        }

    }

}



