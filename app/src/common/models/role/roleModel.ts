namespace common.models {

    export class Role extends AbstractModel{

        protected __primaryKey = 'key';

        public key:string;
        public description:string;
        public isDefault:boolean;
        public type:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}