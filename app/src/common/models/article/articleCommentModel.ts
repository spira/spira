namespace common.models {

    @common.decorators.changeAware
    export class ArticleComment extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _author: User,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        public articleCommentId:string = undefined;
        public body:string = undefined;
        public createdAt:moment.Moment = undefined;
        public _author:common.models.User = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



