namespace app.admin.users.editUser.roles {

    export const namespace = 'app.admin.users.editUser.roles';

    export class RolesController {

        public rolesForm:ng.IFormController;
        public usersPermissions:common.models.role.Permission[];

        public searchText:string;

        static $inject = [
            'fullUserInfo',
            'roles',
        ];

        constructor(
            public user:common.models.User,
            public roles:common.models.Role[]
        ) {

            this.user._roles = this.fillUserRoles(this.user._roles, roles);
            this.refreshPermissions();

        }

        private refreshPermissions() {
            this.usersPermissions = this.listUsersPermissions(this.user);
        }

        private fillUserRoles(userRoles, allRoles):common.models.Role[] {
            return _.map(userRoles, (role:common.models.Role):common.models.Role => {
                return <common.models.Role>_.find(allRoles, {key:role.key});
            });
        }

        private listUsersPermissions(user:common.models.User):common.models.role.Permission[] {

            let userPerms:string[] = _.pluck(user._roles, 'key');

            let allPermissions = _.reduce(userPerms, (currentPermissions:common.models.role.Permission[], roleKey:string):common.models.role.Permission[] => {
                let matchingRole:common.models.Role = _.find(this.roles, {key:roleKey});

                if (!matchingRole){
                    return currentPermissions;
                }

                return currentPermissions.concat(matchingRole._permissions);

            }, []);

            return _.reduce(allPermissions, (currentPermissions:common.models.role.Permission[], permission:common.models.role.Permission) => {

                let match = _.find(currentPermissions, {key: permission.key});

                if (!match){
                    permission.__grantedByAll = [permission.__grantedByRole];

                    currentPermissions.push(permission);
                    return currentPermissions;
                }

                match.__grantedByAll.push(permission.__grantedByRole);

                return currentPermissions;
            }, []);

        }

        public roleSearch(query:string):common.models.Role[]{

            if (!query){
                return [];
            }

            return _.filter(this.roles, (role:common.models.Role) => {
                return _.contains(role.key.toLowerCase(), query.toLowerCase());
            });

        }

        public userHasRole(role:common.models.Role):boolean{
            return _.contains(this.user._roles, role);
        }

        public toggleRole(role:common.models.Role) {
            if (this.userHasRole(role)){
                this.user._roles = _.without(this.user._roles, role);
            }else{
                this.user._roles.push(role);
            }

            this.refreshPermissions();
        }



 }

    angular.module(namespace, [])
        .controller(namespace+'.controller', RolesController);
}




