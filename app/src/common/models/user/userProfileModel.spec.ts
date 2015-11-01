namespace common.models {

    let seededChance = new Chance();

    describe('User Profile Model', () => {

        let userProfileData = {
            dob:seededChance.date(),
            phone:seededChance.phone(),
            mobile:seededChance.phone()
        };

        it('should instantiate a new user profile', () => {

            let userProfile = new UserProfile(userProfileData);

            expect(userProfile).to.be.instanceOf(UserProfile);

        });

    });

}