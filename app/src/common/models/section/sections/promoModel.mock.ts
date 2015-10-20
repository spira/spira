namespace common.models.sections {

    export class PromoMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return Promo;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Promo {
            return <Promo> new this().buildEntity(overrides, exists);
        }

    }

}