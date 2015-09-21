namespace common.models {

    @common.decorators.changeAware
    export class ArticleComment extends AbstractModel {

        public articleCommentId:string = undefined;
        public body:string = undefined;
        public createdAt:string = undefined;
        public _author:common.models.User = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



