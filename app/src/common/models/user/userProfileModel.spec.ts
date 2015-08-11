(() => {

    let seededChance = new Chance(1);

    describe('User Profile Model', () => {

        let userProfileData = {
            dob:seededChance.date(),
            phone:seededChance.phone(),
            mobile:seededChance.phone()
        };

        it('should instantiate a new user profile', () => {

            let userProfile = new common.models.UserProfile(userProfileData);

            expect(userProfile).to.be.instanceOf(common.models.UserProfile);

        });

    });

})();