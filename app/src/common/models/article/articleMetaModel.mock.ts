namespace common.models {

    export class ArticleMetaMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.ArticleMeta;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                metaId: seededChance.guid(),
                articleId: seededChance.guid(),
                metaName: seededChance.pick(['name', 'description', 'keyword', 'canonical', 'other']),
                metaContent: seededChance.string()
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):ArticleMeta {
            return <ArticleMeta> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):ArticleMeta[] {
            return <ArticleMeta[]>new this().buildCollection(count, overrides, exists);
        }

    }

}