namespace common.models {

    @common.decorators.changeAware.changeAware
    export class Image extends AbstractModel{

        public imageId:string;
        public version:number;
        public folder:string;
        public format:string;
        public alt:string;
        public title:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



