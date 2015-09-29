namespace common.models.sections {

    export class ImageMock extends AbstractMock {

        public getModelClass():IModelClass {
            return Image;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                images: {
                    _image: common.models.ImageMock.entity(),
                    caption: seededChance.sentence(),
                    transformations: null
                }
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Image {
            return <Image> new this().buildEntity(overrides, exists);
        }

    }

}