module common.models {

    @common.decorators.changeAware
    export class UserCredential extends AbstractModel implements global.IUserCredential{

        public userId:string;
        public userCredentialId:string;
        public password:string;

        constructor(data:any, exists:boolean = false) {

            super(data, exists);

            this.hydrate(data, exists);
        }

    }

}
