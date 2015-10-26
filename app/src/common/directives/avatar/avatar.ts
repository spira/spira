namespace common.directives.avatar {

    export const namespace = 'common.directives.avatar';

    export interface IAvatarChangedHandler {
        (user:common.models.User):void;
    }

    export interface IAvatarActions {
        action:() => void;
        label:string;
        icon:string;
    }

    export class AvatarController {

        static $inject = ['userService', '$mdDialog', 'notificationService', '$mdBottomSheet'];

        public user:common.models.User;

        private avatarChangedHandler:IAvatarChangedHandler;

        public $scope:ng.IScope;
        public $element:ng.IAugmentedJQuery;

        public avatarActions:IAvatarActions[] = [
            {action:() => {
                this.openChangeAvatarDialog();
            }, label: 'Change Avatar', icon: 'face'},
            {action:() => {
                this.removeAvatar();
            }, label: 'Remove Avatar', icon: 'remove circle'}
        ];

        constructor(
            private userService:common.services.user.UserService,
            private $mdDialog:ng.material.IDialogService,
            private notificationService:common.services.notification.NotificationService,
            private $mdBottomSheet:ng.material.IBottomSheetService
        ) {
        }

        public registerAvatarChangedHandler(handler:IAvatarChangedHandler):void {
            this.avatarChangedHandler = handler;
        }

        public openAvatarActions():ng.IPromise<any> {
            return this.$mdBottomSheet.show({
                parent: this.$element,
                templateUrl: 'templates/common/directives/avatar/avatarActionsBottomSheet.tpl.html',
                scope: this.$scope,
                preserveScope: true
            });
        }

        public openChangeAvatarDialog():ng.IPromise<any> {
            this.$mdBottomSheet.hide();

            return this.$mdDialog.show({
                templateUrl: 'templates/common/directives/avatar/changeAvatarDialog.tpl.html',
                scope: this.$scope,
                preserveScope: true
            })
        }

        public updatedAvatar():ng.IPromise<any> {
            if (this.avatarChangedHandler){
                this.avatarChangedHandler(this.user);
            }

            this.user.avatarImgId = this.user._uploadedAvatar.imageId;

            return this.saveUser();
        }

        public removeAvatar():ng.IPromise<any> {
            if (this.avatarChangedHandler){
                this.avatarChangedHandler(this.user);
            }

            this.user.avatarImgId = null;
            this.user._uploadedAvatar = null;

            return this.saveUser();
        }

        private saveUser():ng.IPromise<any> {

            this.$mdDialog.hide();

            return this.userService.saveUser(this.user)
                .then(() => {
                    this.notificationService.toast('Profile update was successful').pop();
                },
                (err) => {
                    this.notificationService.toast('Profile update was unsuccessful, please try again').pop();
                })

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

            directiveController.$scope = $scope;
            directiveController.$element = $element;

        };

        static factory(): ng.IDirectiveFactory {
            const directive = () => new AvatarDirective();
            return directive;
        }
    }

    angular.module(namespace, [])
        .directive('avatar', AvatarDirective.factory());

}