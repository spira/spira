namespace common.models {

    @common.decorators.changeAware
    export class Article extends AbstractModel{

        protected _nestedEntityMap = {
            tags: Tag
        };

        public articleId:string = undefined;
        public title:string = undefined;
        public permalink:string = undefined;
        public content:string = undefined;
        public primaryImage:string = undefined;
        public status:string = undefined;
        public _metas:common.models.ArticleMeta[];

        public _tags:common.models.Tag[];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

        /**
         * Get the article identifier
         * @returns {string}
         */
        public getIdentifier():string {

            return this.permalink || this.articleId;
        }

    }

}



