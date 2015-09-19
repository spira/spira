namespace common.models {

    let seededChance = new Chance(1);

    describe('User Model', () => {

        let userData:global.IUserData = new UserMock().getMockData();

        it('should instantiate a new user', () => {

            let user = new User(_.clone(userData, true)); // We have to clone the user data so that we get a fresh copy for each test

            expect(user).to.be.instanceOf(User);

        });

        it('should get custom user mock entity', () => {

            let user = new UserMock().entity({userId: 'abc-123'});

            expect(user).to.be.instanceOf(User);

            expect(user.userId).to.equal('abc-123');

        });

        it('should get user mock collection', () => {

            let users = new UserMock().collection(5);

            expect(users).to.be.instanceOf(Array);

            expect(users).to.have.length(5);
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

        it('should be able to check if the user has a social login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataFacebook = new common.models.UserSocialLogin ({
                userId:user.userId,
                provider:common.models.UserSocialLogin.facebookType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            });

            let userLoginDataGoogle = new common.models.UserSocialLogin ({
                userId:user.userId,
                provider:common.models.UserSocialLogin.googleType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            });

            user._socialLogins.push(userLoginDataFacebook, userLoginDataGoogle);

            expect(user.hasSocialLogin(common.models.UserSocialLogin.facebookType)).to.be.true;

        });

        it('should be able to check if the user does not have a social login', () => {

            let user = new User(_.clone(userData, true));

            let userLoginDataGoogle = new common.models.UserSocialLogin({
                userId:user.userId,
                provider:common.models.UserSocialLogin.googleType,
                token:seededChance.apple_token() // Closest thing to a token in Chance JS library
            });

            user._socialLogins.push(userLoginDataGoogle);

            expect(user.hasSocialLogin(common.models.UserSocialLogin.facebookType)).to.be.false;

        });

    });

}