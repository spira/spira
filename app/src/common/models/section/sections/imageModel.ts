namespace common.models.sections {

    export interface IImageContent{
        _image:common.models.Image;
        caption:string;
        size:string;
        alignment:string;
    }

    export interface ISizeOption{
        key:string;
        name:string;
    }

    export interface IAlignmentOption{
        key:string;
        name:string;
    }

    export class Image extends AbstractModel {
        public static contentType = 'image';
        public static alignmentOptions:IAlignmentOption[] = [
            {
                key: 'left',
                name: 'Left',
            },
            {
                key: 'centre',
                name: 'Centre',
            },
            {
                key: 'right',
                name: 'Right',
            }
        ];
        public static sizeOptions:ISizeOption[] = [
            {
                key: 'small',
                name: 'Small (300px)',
            },
            {
                key: 'half',
                name: 'Half Page',
            },
            {
                key: 'full',
                name: 'Full width',
            },
            {
                key: 'oversize',
                name: 'Oversize',
            }
        ];

        public images:IImageContent[] = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



