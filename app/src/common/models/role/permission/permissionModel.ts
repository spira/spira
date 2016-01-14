namespace common.models.role {

    export interface IMatchingRoutePermission {
        method: string;
        uri:string;
    }

    export class Permission extends AbstractModel{

        protected __primaryKey = 'key';

        public key:string;
        public description:string;
        public type:string;
        public matchingRoutes:IMatchingRoutePermission[];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}