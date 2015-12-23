namespace common.models.sections {

    export class RichText extends AbstractModel {
        public static contentType = 'rich_text';

        public body:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



