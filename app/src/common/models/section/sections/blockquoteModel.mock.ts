namespace common.models.sections {

    export class BlockquoteMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return Blockquote;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                body: seededChance.paragraph(),
                author: seededChance.name(),
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Blockquote {
            return <Blockquote> new this().buildEntity(overrides, exists);
        }

    }

}