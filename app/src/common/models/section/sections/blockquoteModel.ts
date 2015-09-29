namespace common.models.sections {

    export class Blockquote extends AbstractModel {

        public static contentType = 'blockquote';

        public body:string = undefined;
        public author:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



