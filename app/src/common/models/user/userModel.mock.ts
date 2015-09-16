namespace common.models {

    export class UserMock{

        private static getMockData():global.IUserData {

            let seededChance = new Chance(Math.random());

            return {
                userId:seededChance.guid(),
                email:seededChance.email(),
                firstName:seededChance.first(),
                lastName:seededChance.last(),
                emailConfirmed:seededChance.date(),
                country:seededChance.country(),
                avatarImgUrl:seededChance.url(),
                regionCode: seededChance.pick(['uk', 'us', 'gb']),
                userType: seededChance.pick(User.userTypes),
                _socialLogins:(<common.models.UserSocialLogin[]>[])
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true) {

            let model = new common.models.User(_.merge(UserMock.getMockData(), overrides));

            model.setExists(exists);
            return model;
        }

        public static collection(count:number = 10){
            return chance.unique(UserMock.entity, count);
        }

    }

}