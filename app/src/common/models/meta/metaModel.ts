namespace common.models {

    @common.decorators.changeAware
    export class Meta extends AbstractModel {

        public metaId:string;
        public metaableId:string;
        public metaableType:string;
        public metaName:string;
        public metaContent:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



