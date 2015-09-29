namespace common.models.sections {

    export class RichTextMock extends AbstractMock {

        public getModelClass():IModelClass {
            return RichText;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                body: seededChance.paragraph(),
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):RichText {
            return <RichText> new this().buildEntity(overrides, exists);
        }

    }

}