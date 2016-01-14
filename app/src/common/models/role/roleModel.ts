namespace common.models {

    export class Role extends AbstractModel{

        static adminRoleKey:string = 'admin';
        static knownRoles:string[] = [Role.adminRoleKey];

        protected __nestedEntityMap:INestedEntityMap = {
            _permissions: role.Permission,
        };

        protected __primaryKey = 'key';

        public key:string;
        public description:string;
        public isDefault:boolean;
        public type:string;

        public _permissions:role.Permission[];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}