namespace common.directives.avatar {

    interface TestScope extends ng.IRootScopeService {
        testUser: common.models.User;
        AvatarController: AvatarController;
    }

    describe('Change avatar controller', () => {

        let $compile:ng.ICompileService,
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            AvatarController:AvatarController,
            directiveScope:TestScope,
            compiledElement: ng.IAugmentedJQuery,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject((_$compile_, $controller, _$rootScope_, _userService_, _$mdDialog_, _notificationService_, _$q_) => {
                $compile = _$compile_;
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                $q = _$q_;
            });

            // Only initialise the directive once to speed up the testing
            if(!AvatarController) {

                directiveScope = <TestScope>$rootScope.$new();

                directiveScope.testUser = common.models.UserMock.entity({
                    _uploadedAvatar: common.models.ImageMock.entity()
                });

                compiledElement = $compile(`
                        <avatar ng-model="testUser"></avatar>
                    `)(directiveScope);

                $rootScope.$digest();

                AvatarController = (<TestScope>compiledElement.isolateScope()).AvatarController;

                (<any>AvatarController).$mdDialog.show = sinon.stub().returns($q.when(true));
                (<any>AvatarController).$mdDialog.hide = sinon.stub();

                (<any>AvatarController).notificationService.toast = () => {
                    return {
                        pop: sinon.stub()
                    }
                };

            }

        });

        describe('Initialization', () => {

            it('should initialise the directive', () => {

                expect($(compiledElement).hasClass('avatar-directive')).to.be.true;

            });

        });

        describe('Dialog functions', () => {

            it('should be able to open the avatar dialog', () => {

                AvatarController.openAvatarDialog();

                $rootScope.$digest();

                expect((<any>AvatarController).$mdDialog.show).to.be.called;
            });

            it('should be able to close the avatar dialog', () => {

                AvatarController.closeAvatarDialog();

                $rootScope.$digest();

                expect((<any>AvatarController).$mdDialog.hide).to.be.called;
            });

        });

        describe('Avatar functions', () => {

            beforeEach(() => {

                sinon.spy((<any>AvatarController).notificationService, 'toast');

            });

            afterEach(() => {

                (<any>AvatarController).notificationService.toast.restore();

            });

            it('should be able to save the update to the avatar', () => {

                (<any>AvatarController).userService.saveUser = sinon.stub().returns($q.when(true));

                AvatarController.updatedAvatar();

                $rootScope.$digest();

                expect(AvatarController.user.avatarImgId).to.equal(AvatarController.user._uploadedAvatar.imageId);

                expect((<any>AvatarController).$mdDialog.hide).to.be.called;

                expect((<any>AvatarController).userService.saveUser).to.be.calledWith(AvatarController.user);

                expect((<any>AvatarController).notificationService.toast).to.be.calledWith('Profile update was successful');

            });

            it('should alert when the user was not able to be saved', () => {

                (<any>AvatarController).userService.saveUser = sinon.stub().returns($q.reject());

                AvatarController.updatedAvatar();

                $rootScope.$digest();

                expect((<any>AvatarController).notificationService.toast).to.be.calledWith('Profile update was unsuccessful, please try again');

            });

            it('should be able to remove the avatar', () => {

                (<any>AvatarController).userService.saveUser = sinon.stub().returns($q.when(true));

                let removePromise = AvatarController.removeAvatar();

                $rootScope.$digest();

                expect((<any>AvatarController).$mdDialog.show).to.be.called;

                removePromise.then(() => {

                    expect(AvatarController.user.avatarImgId).to.equal(null);

                    expect(AvatarController.user._uploadedAvatar).to.equal(null);

                    expect((<any>AvatarController).userService.saveUser).to.be.calledWith(AvatarController.user);

                    expect((<any>AvatarController).notificationService.toast).to.be.calledWith('Profile update was successful');

                });

            });

        });

    });

}