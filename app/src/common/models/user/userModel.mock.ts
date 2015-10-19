namespace common.models {

    export class UserMock extends AbstractMock{

        public getModelClass():IModelClass {
            return common.models.User;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                userId:seededChance.guid(),
                email:seededChance.email(),
                firstName:seededChance.first(),
                lastName:seededChance.last(),
                emailConfirmed: moment(seededChance.date()).toISOString(),
                country:seededChance.country(),
                avatarImgUrl:seededChance.url(),
                regionCode: seededChance.pick(['uk', 'us', 'gb']),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):User {
            return <User> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):User[] {
            return <User[]>new this().buildCollection(count);
        }

    }

}