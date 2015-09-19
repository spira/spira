namespace common.models {

    export class UserProfileMock extends AbstractMock {

        public getModelClass():IModelClass {
            return UserProfile;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                dob:'1921-01-01',
                mobile:'04123123',
                phone:'',
                gender:'M',
                about:'Lorem',
                facebook:'',
                twitter:'',
                pinterest:'',
                instagram:'',
                website:''
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):UserProfile {
            return <UserProfile> new this().buildEntity(overrides, exists);
        }

    }

}