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

        public articleCommentId:string;
        public body:string;
        public createdAt:moment.Moment;
        public _author:common.models.User;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



