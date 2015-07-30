module common.models {

    export class User implements global.IUserData{

        public userId:string;
        public email:string;
        public firstName:string;
        public lastName:string;
        public _userCredential:global.IUserCredential;

        constructor(data:global.IUserData) {

            _.assign(this, data);

        }

        public fullName() {
            return this.firstName + ' ' + this.lastName;
        }

    }
    //
    //angular.module(namespace, [])
    //    .service('userService', UserService);

}



