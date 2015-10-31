namespace common.models {

    let seededChance = new Chance();

    describe('User Login Model', () => {

        let userLoginData = {
            userId:seededChance.guid(),
            provider:seededChance.pick(UserSocialLogin.providerTypes),
            token:seededChance.apple_token() // Closest thing to a token in Chance JS library
        };

        it('should instantiate a new user login', () => {

            let userLogin = new UserSocialLogin(userLoginData);

            expect(userLogin).to.be.instanceOf(UserSocialLogin);

        });

    });

}