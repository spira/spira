namespace common.directives.avatar.dialog {

    export const namespace = 'common.directives.avatar.dialog';

    export class ChangeAvatarDialogController {

        static $inject = ['$mdDialog', 'uploadedAvatar'];

        constructor(
            private $mdDialog:ng.material.IDialogService,
            private uploadedAvatar:common.models.Image
        ) {
        }

        /**
         * User has just uploaded a new avatar, pass the new user object back to the directive
         * controller.
         */
        public updatedAvatar():void {

            this.$mdDialog.hide(this.uploadedAvatar);

        }
    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', ChangeAvatarDialogController);

}
