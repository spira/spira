namespace common.models.role {

    export interface IMatchingRoutePermission {
        method: string;
        uri:string;
    }

    export class Permission extends AbstractModel {

        protected __primaryKey = 'key';

        public key:string;
        public description:string;
        public type:string;
        public matchingRoutes:IMatchingRoutePermission[];

        public __grantedByRole:Role;
        public __grantedByAll:Role[];
        public __grantedByRoleNames:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

        public getGrantedByRoleNames():string {
            return _.pluck(this.__grantedByAll, 'key').join(', ');
        }

    }

}