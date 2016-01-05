namespace common.models {

    @common.decorators.changeAware.changeAware
    export class Comment extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _author: User,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        public commentId:string;
        public body:string;
        public createdAt:moment.Moment;
        public _author:common.models.User;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



