namespace common.models {

    @changeAware
    export class User extends Model implements global.IUserData {

        public userId:string;
        public email:string;
        public firstName:string;
        public lastName:string;
        public _userCredential:global.IUserCredential;
        public emailConfirmed:string;

        constructor(data:global.IUserData) {

            super(data);

            _.assign(this, data);

        }

        public fullName() {
            return this.firstName + ' ' + this.lastName;
        }

    }

}



