namespace common.models {

    export class Role extends AbstractModel{

        static adminRoleKey:string = 'admin';
        static knownRoles:string[] = [Role.adminRoleKey];

        protected __nestedEntityMap:INestedEntityMap = {
            _permissions: this.hydratePermissions,
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

        /**
         * Hydrate the permission models, referencing the parent role each time
         * @param data
         * @param exists
         * @returns {role.Permission[]}
         */
        private hydratePermissions(data:any, exists:boolean):common.models.role.Permission[] {

            return _.map(data['_permissions'], (entityData) => {

                let permission:role.Permission = <any>this.hydrateModel(entityData, role.Permission, exists);

                if (!_.isArray(permission.__grantedBy)){
                    permission.__grantedBy = [];
                }
                permission.__grantedBy.push(this);
                return permission;

            });

        }

    }

}