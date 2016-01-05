namespace common.models {

    @common.decorators.changeAware.changeAware
    export class Section<T extends AbstractModel> extends AbstractModel {

        public __contentTypeMap = {
            [sections.RichText.contentType]: sections.RichText,
            [sections.Blockquote.contentType]: sections.Blockquote,
            [sections.Media.contentType]: sections.Media,
            [sections.Promo.contentType]: sections.Promo,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __nestedEntityMap:INestedEntityMap = {
            content: this.hydrateSection,
            _localizations: Localization,
        };

        public sectionId:string;
        public content:T;
        public type:string;
        public createdAt:moment.Moment;
        public updatedAt:moment.Moment;

        public _localizations:Localization<Section<T>>[] = [];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

        public static getContentTypeMap(){
            return {
                [sections.RichText.contentType]: sections.RichText,
                [sections.Blockquote.contentType]: sections.Blockquote,
                [sections.Media.contentType]: sections.Media,
                [sections.Promo.contentType]: sections.Promo,
            };
        }


        private hydrateSection(data:any, exists:boolean):sections.RichText|sections.Blockquote|sections.Media|sections.Promo {

            let SectionClass = Section.getContentTypeMap()[data.type];

            return new SectionClass(data.content, exists);
        }

    }

}



