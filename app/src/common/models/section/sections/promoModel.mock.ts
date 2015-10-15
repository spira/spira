namespace common.models.sections {

    export class PromoMock extends AbstractMock {

        public getModelClass():IModelClass {
            return Promo;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Promo {
            return <Promo> new this().buildEntity(overrides, exists);
        }

    }

}