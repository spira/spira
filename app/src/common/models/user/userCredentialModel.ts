module common.models {

    @common.decorators.changeAware
    export class UserCredential extends AbstractModel implements global.IUserCredential{

        public userCredentialId:string = undefined;
        public password:string = undefined;

        constructor(data:any, exists:boolean = false) {

            super(data, exists);

            this.hydrate(data, exists);
        }

    }

}
