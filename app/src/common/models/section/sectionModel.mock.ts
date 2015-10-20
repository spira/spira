namespace common.models {

    export class SectionMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Section;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            let type = seededChance.pick(_.keys(Section.getContentTypeMap()));

            return {
                sectionId: seededChance.guid(),
                type: type,
                content: SectionMock.getContentTypeMap()[type].entity(),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Section<any> {
            return <Section<any>> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Section<any>[] {
            return <Section<any>[]>new this().buildCollection(count, overrides, exists);
        }

        public static getContentTypeMap(){
            return {
                [sections.RichText.contentType]: sections.RichTextMock,
                [sections.Blockquote.contentType]: sections.BlockquoteMock,
                [sections.Image.contentType]: sections.ImageMock,
                [sections.Promo.contentType]: sections.PromoMock,
            };
        }

    }

}