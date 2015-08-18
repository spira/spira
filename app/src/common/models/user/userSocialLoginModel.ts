module common.models {

    export class UserSocialLogin implements IModel {

        public static googleType = 'google';
        public static facebookType = 'facebook';
        public static providerTypes:string[] = [UserSocialLogin.googleType, UserSocialLogin.facebookType];

        public userId:string = undefined;
        public provider:string = undefined;
        public token:string = undefined;

        constructor(data:any) {
            _.assign(this, data);
        }

    }

}
