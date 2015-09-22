namespace common.models {

    export class ArticleCommentMock extends AbstractMock {

        public getModelClass():IModelClass {
            return common.models.ArticleComment;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                articleCommentId: seededChance.guid(),
                body: seededChance.paragraph(),
                createdAt: moment(seededChance.date())
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):ArticleComment {
            return <ArticleComment> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):ArticleComment[] {
            return <ArticleComment[]>new this().buildCollection(count);
        }

    }

}