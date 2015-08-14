namespace common.models {

    let seededChance = new Chance(1);

    describe('User Model', () => {

        let userData:global.IUserData = {
            userId:seededChance.guid(),
            email:seededChance.email(),
            firstName:seededChance.first(),
            lastName:seededChance.last(),
            emailConfirmed:seededChance.date(),
            country:seededChance.country(),
            avatarImgUrl:seededChance.url(),
            userType: seededChance.pick(User.userTypes)
        };

        it('should instantiate a new user', () => {

            let user = new User(userData);

            expect(user).to.be.instanceOf(User);

        });

        it('should return the user\'s full name', () => {

            let user = new User(userData);

            expect(user.fullName()).to.equal(userData.firstName + ' ' + userData.lastName);

        });

        it('should be able to check if the user is an administrator', () => {

            userData.userType = 'admin';

            let user = new User(userData);

            expect(user.isAdmin()).to.be.true;

        });

    });

}