namespace common.models.sections {

    export class Promo extends AbstractModel {

        public static contentType = 'promo';

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



