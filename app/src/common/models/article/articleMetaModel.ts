namespace common.models {

    @common.decorators.changeAware
    export class ArticleMeta extends AbstractModel {

        public articleId:string = undefined;
        public metaName:string = undefined;
        public metaContent:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



