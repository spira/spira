namespace common.models {

    export class TagMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Tag;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                tagId: seededChance.guid(),
                tag: seededChance.word()
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Tag {
            return <Tag> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Tag[] {
            return <Tag[]>new this().buildCollection(count, overrides, exists);
        }

    }

}