namespace common.models {

    @common.decorators.changeAware
    export class Section extends AbstractModel {

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __nestedEntityMap:INestedEntityMap = {
            content: this.hydrateSection,
        };

        public sectionId:string;
        public content:sections.RichText|sections.Blockquote|sections.Image;
        public type:string;
        public createdAt:moment.Moment = undefined;
        public updatedAt:moment.Moment = undefined;


        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }


        private hydrateSection(data:any, exists:boolean):sections.RichText|sections.Blockquote|sections.Image {

            switch(data.type){
                case 'rich_text':
                    return new sections.RichText(data.content, exists);
                break;
                case 'blockquote':
                    return new sections.Blockquote(data.content, exists);
                break;
                case 'image':
                    return new sections.Image(data.content, exists);
                break;
            }

            throw new SpiraException("Invalid type for content piece");

        }

    }

}



