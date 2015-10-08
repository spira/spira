namespace common.models {

    @common.decorators.changeAware
    export class Section<T extends AbstractModel> extends AbstractModel {

        public __contentTypeMap = {
            [sections.RichText.contentType]: sections.RichText,
            [sections.Blockquote.contentType]: sections.Blockquote,
            [sections.Image.contentType]: sections.Image,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __nestedEntityMap:INestedEntityMap = {
            content: this.hydrateSection,
        };

        public sectionId:string;
        public content:T;
        public type:string;
        public createdAt:moment.Moment = undefined;
        public updatedAt:moment.Moment = undefined;


        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

        public static getContentTypeMap(){
            return {
                [sections.RichText.contentType]: sections.RichText,
                [sections.Blockquote.contentType]: sections.Blockquote,
                [sections.Image.contentType]: sections.Image,
            };
        }


        private hydrateSection(data:any, exists:boolean):sections.RichText|sections.Blockquote|sections.Image {

            let SectionClass = Section.getContentTypeMap()[data.type];

            return new SectionClass(data.content, exists);
        }

    }

}



