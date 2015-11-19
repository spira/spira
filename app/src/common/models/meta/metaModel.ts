namespace common.models {

    @common.decorators.changeAware
    export class Meta extends AbstractModel {

        public metaId:string = undefined;
        public metaableId:string = undefined;
        public metaableType:string = undefined;
        public metaName:string = undefined;
        public metaContent:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



