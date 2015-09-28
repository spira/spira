namespace common.models.sections {

    export interface IImageContent{
        _image:common.models.Image;
        caption:string;
        transformations:string;
    }

    export class Image extends AbstractModel {

        public images:IImageContent[] = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



