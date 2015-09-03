namespace common.models {

    @common.decorators.changeAware
    export class Image extends AbstractModel{

        public imageId:string = undefined;
        public version:number = undefined;
        public folder:string = undefined;
        public format:string = undefined;
        public alt:string = undefined;
        public title:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



