module common.models {

    export class UserSocialLogin extends AbstractModel {

        public static googleType = 'google';
        public static facebookType = 'facebook';
        public static providerTypes:string[] = [UserSocialLogin.googleType, UserSocialLogin.facebookType];

        public userId:string = undefined;
        public provider:string = undefined;
        public token:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}
