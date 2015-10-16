namespace common.models.sections {

    export class ImageMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return Image;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                images: {
                    _image: common.models.ImageMock.entity(),
                    caption: seededChance.sentence(),
                    size: _.pick<common.models.sections.ISizeOption, common.models.sections.ISizeOption[]>(common.models.sections.Image.sizeOptions).key,
                    alignment: _.pick<common.models.sections.IAlignmentOption, common.models.sections.IAlignmentOption[]>(common.models.sections.Image.alignmentOptions).key,
                }
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Image {
            return <Image> new this().buildEntity(overrides, exists);
        }

    }

}