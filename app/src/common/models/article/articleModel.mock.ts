namespace common.models {

    export class ArticleMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Article;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                articleId: seededChance.guid(),
                title: seededChance.string(),
                shortTitle: seededChance.word(),
                permalink: seededChance.url(),
                primaryImage: seededChance.url(),
                status: seededChance.pick(['draft', 'published', 'ready']),
                authorId: seededChance.guid(),
                authorDisplay: seededChance.bool(),
                showAuthorPromo: seededChance.bool(),
                _articleMetas: [],
                _tags: [],
                _comments: [],
                _sections: [],
                _localizations: [],
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Article {
            return <Article> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Article[] {
            return <Article[]>new this().buildCollection(count, overrides, exists);
        }

    }

}