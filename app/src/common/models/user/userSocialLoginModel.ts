module common.models {

    export class UserSocialLogin extends AbstractModel implements global.ISocialLogin {

        public static googleType = 'google';
        public static facebookType = 'facebook';
        public static providerTypes:string[] = [UserSocialLogin.googleType, UserSocialLogin.facebookType];

        public userId:string;
        public provider:string;
        public token:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}
