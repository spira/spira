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
            userType: seededChance.pick(User.userTypes),
            _socialLogins:(<common.models.UserSocialLogin[]>[])
        };

        it('should instantiate a new user', () => {

            let user = new User(_.clone(userData, true)); // We have to clone the user data so that we get a fresh copy for each test

            expect(user).to.be.instanceOf(User);

        });

        it('should return the user\'s full name', () => {

            let user = new User(_.clone(userData, true));

            expect(user.fullName()).to.equal(userData.firstName + ' ' + userData.lastName);

        });

        it('should be able to check if the user is an administrator', () => {

            userData.userType = 'admin';

            let user = new User(_.clone(userData, true));

            expect(user.isAdmin()).to.be.true;

        });

        it('should be able to check if the user has Facebook login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataFacebook:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.facebookType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            let userLoginDataGoogle:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.googleType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            user._socialLogins.push(userLoginDataFacebook, userLoginDataGoogle);

            expect(user.hasFacebookLogin()).to.be.true;

        });

        it('should be able to check if the user does not have a Facebook login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataGoogle:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.googleType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            user._socialLogins.push(userLoginDataGoogle);

            expect(user.hasFacebookLogin()).to.be.false;

        });

        it('should be able to check if the user has Google login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataFacebook:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.facebookType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            let userLoginDataGoogle:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.googleType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            user._socialLogins.push(userLoginDataFacebook, userLoginDataGoogle);

            expect(user.hasGoogleLogin()).to.be.true;

        });

        it('should be able to check if the user does not have a Google login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataFacebook:common.models.UserSocialLogin = {
                userId:user.userId,
                provider:common.models.UserSocialLogin.facebookType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            };

            user._socialLogins.push(userLoginDataFacebook);

            expect(user.hasGoogleLogin()).to.be.false;

        });

    });

}