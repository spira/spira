namespace common.models {

    export class ArticleMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Article;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            let authorOverride = seededChance.bool();

            let author = common.models.UserMock.entity();

            return {
                postId: seededChance.guid(),
                title: seededChance.string(),
                shortTitle: seededChance.word(),
                permalink: chance.word({syllables: 3}),
                status: seededChance.pick(['draft', 'published', 'ready']),
                authorId: author.userId,
                thumbnailImageId: seededChance.guid(),
                authorOverride: authorOverride ? seededChance.name() : null,
                authorWebsite: authorOverride ? seededChance.url() : null,
                showAuthorPromo: seededChance.bool(),
                _metas: [],
                _tags: [],
                _comments: [],
                _sections: [],
                _localizations: [],
                _author: author
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