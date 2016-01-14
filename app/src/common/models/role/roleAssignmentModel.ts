namespace common.models {

    export class RoleAssignment extends AbstractModel{

        static adminRoleKey:string = 'admin';
        static knownRoles:string[] = [RoleAssignment.adminRoleKey];

        public roleKey: string;
        public userId: string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}