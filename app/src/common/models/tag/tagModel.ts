namespace common.models {

    @common.decorators.changeAware
    export class Tag extends AbstractModel{

        public tagId:string = undefined;
        public tag:string = undefined;

        constructor(data:any) {

            super(data);

            _.assign(this, data);

        }

    }

}



