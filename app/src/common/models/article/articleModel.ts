module common.models {

    export class Article implements IModel{

        public articleId:string;
        public title:string;
        public permalink:string;
        public content:string;

        constructor(data:any) {

            _.assign(this, data);
        }

    }

}



