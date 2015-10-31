(() => {

    let seededChance = new Chance();

    describe('User Login Model', () => {

        let userLoginData = {
            userId:seededChance.guid(),
            provider:seededChance.pick(common.models.UserSocialLogin.providerTypes),
            token:seededChance.apple_token() // Closest thing to a token in Chance JS library
        };

        it('should instantiate a new user login', () => {

            let userLogin = new common.models.UserSocialLogin(userLoginData);

            expect(userLogin).to.be.instanceOf(common.models.UserSocialLogin);

        });

    });

})();