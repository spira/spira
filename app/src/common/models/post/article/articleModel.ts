namespace common.models {

    @common.decorators.changeAware.changeAware
    export class Article extends Post {

        static __shortcode:string = 'article';

        public shortTitle:string;

        protected __metaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



