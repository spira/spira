namespace common.models {

    export class MetaMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Meta;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                metaId: seededChance.guid(),
                metaName: seededChance.pick(['name', 'description', 'keyword', 'canonical', 'other']),
                metaContent: seededChance.string()
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Meta {
            return <Meta> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Meta[] {
            return <Meta[]>new this().buildCollection(count, overrides, exists);
        }

    }

}