namespace common.models {

    export class SectionMock extends AbstractMock {

        public getModelClass():IModelClass {
            return common.models.Section;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            let type = seededChance.pick(_.keys(Section.getContentTypeMap()));

            return {
                sectionId: seededChance.guid(),
                type: type,
                content: SectionMock.getContentTypeMap()[type].entity(),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Section {
            return <Section> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):Section[] {
            return <Section[]>new this().buildCollection(count);
        }

        public static getContentTypeMap(){
            return {
                [sections.RichText.contentType]: sections.RichTextMock,
                [sections.Blockquote.contentType]: sections.BlockquoteMock,
                [sections.Image.contentType]: sections.ImageMock,
            };
        }

    }

}