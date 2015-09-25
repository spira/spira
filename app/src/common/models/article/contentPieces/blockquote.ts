namespace common.models.contentPieces {

    export class RichText extends AbstractModel {

        public body:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



