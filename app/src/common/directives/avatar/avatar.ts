namespace common.directives.avatar {

    export const namespace = 'common.directives.avatar';

    export interface IAvatarChangedHandler {
        (user:common.models.User):void;
    }

    export class AvatarController {

        static $inject = ['userService', '$mdDialog', 'notificationService'];

        public user:common.models.User;

        private avatarChangedHandler:IAvatarChangedHandler;

        public $scope:ng.IScope;
        public $element:ng.IAugmentedJQuery;

        public canEdit:boolean;

        constructor(
            private userService:common.services.user.UserService,
            private $mdDialog:ng.material.IDialogService,
            private notificationService:common.services.notification.NotificationService
        ) {
            if(typeof this.canEdit === 'undefined') {
                this.canEdit = false;
            }
        }

        public registerAvatarChangedHandler(handler:IAvatarChangedHandler):void {
            this.avatarChangedHandler = handler;
        }

        /**
         * Action called when the profile picture is clicked on.
         * @returns {angular.IPromise<any>}
         */
        public openAvatarDialog():ng.IPromise<any> {

            return this.$mdDialog.show({
                templateUrl: 'templates/common/directives/avatar/changeAvatarDialog.tpl.html',
                scope: this.$scope,
                preserveScope: true,
                clickOutsideToClose: true
            })

        }

        /**
         * Action called when the close button is clicked.
         */
        public closeAvatarDialog():void {

            this.$mdDialog.hide();

        }

        /**
         * Action called by upload-image directive when the avatar has been uploaded.
         * @returns {ng.IPromise<any>}
         */
        public updatedAvatar():ng.IPromise<any> {
            if (this.avatarChangedHandler){
                this.avatarChangedHandler(this.user);
            }

            this.user.avatarImgId = this.user._uploadedAvatar.imageId;

            return this.saveUser();
        }

        /**
         * Action bound to the remove avatar button.
         * @returns {IPromise<TResult>}
         */
        public removeAvatar():ng.IPromise<any> {

            var confirm = this.$mdDialog.confirm()
                .title("Are you sure you want to remove your avatar?")
                .content("This action <strong>cannot</strong> be undone.")
                .ariaLabel("Confirm remove")
                .ok("Remove")
                .cancel("Don't remove it");

            return this.$mdDialog.show(confirm).then(() => {

                if (this.avatarChangedHandler){
                    this.avatarChangedHandler(this.user);
                }

                this.user.avatarImgId = null;
                this.user._uploadedAvatar = null;

                return this.saveUser();

            });

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
            canEdit: '=?'
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