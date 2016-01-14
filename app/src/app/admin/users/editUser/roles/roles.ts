namespace app.admin.users.editUser.roles {

    export const namespace = 'app.admin.users.editUser.roles';

    export class RolesController {

        public rolesForm:ng.IFormController;
        public usersPermissions:common.models.role.Permission[];

        static $inject = [
            'fullUserInfo',
            'roles',
        ];

        constructor(
            public user:common.models.User,
            public roles:common.models.Role[]
        ) {

            this.usersPermissions = this.listUsersPermissions(user);
        }


        private listUsersPermissions(user:common.models.User):common.models.role.Permission[] {

            let userPerms = _.pluck(user._roles, 'key');

            return _.reduce(userPerms, (currentPermissions:common.models.role.Permission[], roleKey:string):common.models.role.Permission[] => {
                let matchingRole:common.models.Role = _.find(this.roles, {key:roleKey});

                if (!matchingRole){
                    return currentPermissions;
                }

                return currentPermissions.concat(matchingRole._permissions);

            }, []);

        }


    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', RolesController);
}




