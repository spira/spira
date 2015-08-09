(() => {

    let seededChance = new Chance(1);

    describe('User Model', () => {

        let userData:global.IUserData = {
            userId:seededChance.guid(),
            email:seededChance.email(),
            firstName:seededChance.first(),
            lastName:seededChance.last(),
        };

        it('should instantiate a new user', () => {

            let user = new common.models.User(userData);

            expect(user).to.be.instanceOf(common.models.User);

        });

        it('should return the user\'s full name', () => {

            let user = new common.models.User(userData);

            expect(user.fullName()).to.equal(userData.firstName + ' '+userData.lastName);

        });

    });

})();