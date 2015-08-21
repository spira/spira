namespace common.models {

    @common.decorators.changeAware
    export class ArticleMeta extends AbstractModel {

        public metaName:string;
        public metaContent:string;
        public metaProperty:string;

        constructor(data:any) {

            super(data);

            _.assign(this, data);

        }

    }

}



