namespace common.models.contentPieces {

    export class Blockquote extends AbstractModel {

        public body:string = undefined;
        public author:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



