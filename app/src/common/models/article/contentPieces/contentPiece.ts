namespace common.models.contentPieces {

    @common.decorators.changeAware
    export class ContentPiece extends AbstractModel {

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __nestedEntityMap:INestedEntityMap = {
            content: this.hydrateContentPieceType,
        };

        public articleContentPieceId:string;
        public articleId:string;
        public content:RichText|Blockquote|Image;
        public type:string;
        public createdAt:moment.Moment = undefined;
        public updatedAt:moment.Moment = undefined;


        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }


        private hydrateContentPieceType(data:any, exists:boolean):RichText|Blockquote|Image {

            switch(data.type){
                case 'rich_text':
                    return new RichText(data.content, exists);
                break;
                case 'blockquote':
                    return new Blockquote(data.content, exists);
                break;
                case 'image':
                    return new Image(data.content, exists);
                break;
            }

            throw new SpiraException("Invalid type for content piece");

        }

    }

}



