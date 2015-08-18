namespace common.services.auth {

    let seededChance = new Chance(1),
        authService:common.services.auth.AuthService,
        ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
        $q:ng.IQService,
        $location:ng.ILocationService,
        $timeout:ng.ITimeoutService,
        $mdDialog:ng.material.IDialogService,
        $state:ng.ui.IStateService,
        notificationService:common.services.notification.NotificationService,
        ngRestAdapter:NgRestAdapter.INgRestAdapterService,
        $window:ng.IWindowService,
        $httpBackend:ng.IHttpBackendService,
        fixtures = {
            buildUser: (overrides = {}) => {

                let userId = seededChance.guid();
                let defaultUser:global.IUserData = {
                    _self: '/users/'+userId,
                    userId: userId,
                    email: seededChance.email(),
                    firstName: seededChance.first(),
                    lastName: seededChance.last(),
                    _userCredential: {
                        userCredentialId: seededChance.guid(),
                        password: seededChance.string(),
                    }
                };

                return _.merge(defaultUser, overrides);
            },
            get user():common.models.User {
                return new common.models.User(fixtures.buildUser());
            }
        };

    describe('Auth Service', () => {

        beforeEach(()=> {

            module('app');

            module(($provide) => {

                $window = <any> {
                    // $window.location.href will update that empty object
                    location: {},
                    // Required functions
                    document: window.document,
                    localStorage: window.localStorage,
                    encodeURIComponent: (<any>window).encodeURIComponent
                };

                // We register our new $window instead of the old
                $provide.constant('$window', $window);

            });

            inject((_$httpBackend_, _authService_, _ngJwtAuthService_, _$q_, _$location_, _$timeout_, _$mdDialog_, _$state_, _notificationService_, _ngRestAdapter_) => {

                if (!authService) { // Don't rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    authService = _authService_;
                    ngJwtAuthService = _ngJwtAuthService_;
                    $q = _$q_;
                    $location = _$location_;
                    $timeout = _$timeout_;
                    $mdDialog = _$mdDialog_;
                    $state = _$state_;
                    notificationService = _notificationService_;
                    ngRestAdapter = _ngRestAdapter_;
                }

            });

        });

        it('should be able to log in using a social network', () => {

            let provider = common.models.UserSocialLogin.facebookType,
                state = 'app.user.profile',
                params = null,
                url = '/auth/social/facebook?returnUrl=%2Fprofile';

            authService.socialLogin(provider, state, params);

            expect($window.location.href).to.equal(url);

        });

        it('should be able to unlink a social network', () => {

            let user = _.clone(fixtures.user),
                provider = common.models.UserSocialLogin.facebookType;

            $httpBackend.expectDELETE('/api/users/' + user.userId + '/socialLogin/' + provider).respond(204);

            let unlinkSocialPromise = authService.unlinkSocialLogin(user, provider);

            expect(unlinkSocialPromise).eventually.to.be.fulfilled;

            $httpBackend.flush();

        });

    });

}