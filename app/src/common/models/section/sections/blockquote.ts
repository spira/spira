namespace common.models.sections {

    export class RichText extends AbstractModel {

        public body:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



