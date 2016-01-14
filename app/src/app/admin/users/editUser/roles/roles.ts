namespace app.admin.users.editUser.roles {

    export const namespace = 'app.admin.users.editUser.roles';

    export class RolesController {

        public rolesForm:ng.IFormController;

        static $inject = [
            'fullUserInfo',
            'roles',
        ];

        constructor(
            public user:common.models.User,
            public roles:any[]
        ) {
        }
    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', RolesController);
}




