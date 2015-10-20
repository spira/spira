namespace common.models {

    export class UserProfileMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return UserProfile;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                dob: moment(seededChance.birthday()).toDate(),
                mobile: seededChance.phone({ mobile: true }),
                phone: seededChance.phone(),
                gender: seededChance.pick(_.pluck(UserProfile.genderOptions, 'value')),
                about: seededChance.paragraph(),
                facebook: seededChance.url({domain: 'www.facebook.com'}),
                twitter: seededChance.twitter(),
                pinterest: seededChance.url({domain: 'www.pintrest.com'}),
                instagram: seededChance.url({domain: 'www.instagram.com'}),
                website: seededChance.url()
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):UserProfile {
            return <UserProfile> new this().buildEntity(overrides, exists);
        }

    }

}