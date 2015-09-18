namespace common.models {

    @common.decorators.changeAware
    export class Tag extends AbstractModel{

        public tagId:string = undefined;
        public tag:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



