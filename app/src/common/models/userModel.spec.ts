///<reference path="../../../build/js/declarations.d.ts" />

(() => {

    let seededChance = new Chance(1);

    describe('User Model', () => {

        it('should return the user\'s full name', () => {

            let userData:global.IUserData = {
                userId:seededChance.guid(),
                email:seededChance.email(),
                firstName:seededChance.first(),
                lastName:seededChance.last(),
            };

            let user = new common.models.User(userData);

            expect(user.fullName()).to.equal(userData.firstName + ' '+userData.lastName);


        });

    });

})();