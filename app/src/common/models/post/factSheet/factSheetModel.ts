namespace common.models {

    @common.decorators.changeAware.changeAware
    export class FactSheet extends Post {

        static __shortcode:string = 'factSheet';

        protected __metaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}