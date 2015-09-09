namespace common.models {

    @common.decorators.changeAware
    export class ArticleMeta extends AbstractModel {

        public metaName:string = undefined;
        public metaContent:string = undefined;
        public metaProperty:string = undefined;
        public newTag:boolean = false; // This is used to keep track of what tags have been saved to the database

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



