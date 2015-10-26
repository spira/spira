namespace common.directives.avatar {

    export const namespace = 'common.directives.avatar';

    export interface IAvatarChangedHandler {
        (user:common.models.User):void;
    }

    export class AvatarController {

        static $inject = ['userService', '$mdDialog', 'notificationService'];

        public user:common.models.User;

        private avatarChangedHandler:IAvatarChangedHandler;

        constructor(
            private userService:common.services.user.UserService,
            private $mdDialog:ng.material.IDialogService,
            private notificationService:common.services.notification.NotificationService
        ) {
        }

        public registerAvatarChangedHandler(handler:IAvatarChangedHandler):void {
            this.avatarChangedHandler = handler;
        }

        public openChangeAvatarDialog():ng.IPromise<any> {

            return this.$mdDialog.show({
                templateUrl: 'templates/common/directives/avatar/dialog/changeAvatarDialog.tpl.html',
                controller: namespace+'.dialog.controller',
                controllerAs: 'ChangeAvatarDialogController',
                clickOutsideToClose: true,
                locals: {
                    uploadedAvatar: this.user._uploadedAvatar
                }
            })
                .then((uploadedAvatar:common.models.Image) => {
                    if (this.avatarChangedHandler){
                        this.avatarChangedHandler(this.user);
                    }

                    this.user._uploadedAvatar = uploadedAvatar;
                    this.user.avatarImgId = uploadedAvatar.imageId;

                    // Update user data
                    return this.userService.saveUser(this.user)
                        .then(() => {
                            this.notificationService.toast('Profile update was successful').pop();
                        },
                        (err) => {
                            this.notificationService.toast('Profile update was unsuccessful, please try again').pop();
                        })
                });
        }

    }

    class AvatarDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['ngModel', 'avatar'];
        public templateUrl = 'templates/common/directives/avatar/avatar.tpl.html';
        public replace = true;
        public scope = {
        };

        public controllerAs = 'AvatarController';
        public controller = AvatarController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ng.INgModelController, AvatarController]) => {

            let $ngModelController = $controllers[0];
            let directiveController = $controllers[1];

            directiveController.registerAvatarChangedHandler((user:common.models.User) => {
                $ngModelController.$setViewValue(user);

                $ngModelController.$setDirty();
            });

            $ngModelController.$render = () => {

                directiveController.user = $ngModelController.$modelValue;
            };

        };

        static factory(): ng.IDirectiveFactory {
            const directive = () => new AvatarDirective();
            return directive;
        }
    }

    angular.module(namespace, [
        'common.directives.avatar.dialog',
    ])
        .directive('avatar', AvatarDirective.factory());

}