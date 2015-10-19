namespace common.models {

    export class ArticleCommentMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.ArticleComment;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                articleCommentId: seededChance.guid(),
                body: seededChance.paragraph(),
                createdAt: moment(seededChance.date())
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):ArticleComment {
            return <ArticleComment> new this().buildEntity(overrides, exists);
        }

        // Commented out as this method is unused and reducing code coverage
        //public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):ArticleComment[] {
        //    return <ArticleComment[]>new this().buildCollection(count, overrides, exists);
        //}

    }

}