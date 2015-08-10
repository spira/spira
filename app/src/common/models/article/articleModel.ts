namespace common.models {

    @changeAware
    export class Article implements IModel{

        public articleId:string;
        public title:string;
        public permalink:string;
        public content:string;
        public primaryImage:string;
        public status:string;

        constructor(data:any) {

            _.assign(this, data);

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



