namespace common.models.sections {

    export interface IMediaContent{
        type:string;
    }

    export interface IImageContent extends IMediaContent {
        _image:common.models.Image;
        caption:string;
    }

    export interface IVideoContent extends IMediaContent {
        videoId:string;
        provider:string;
        caption?:string;
    }

    export interface IVideoProvider{
        providerKey: string;
        validationRegex:RegExp;
        idLength:number;
    }

    export interface ISizeOption{
        key:string;
        name:string;
    }

    export interface IAlignmentOption{
        key:string;
        name:string;
    }

    export class Media extends AbstractModel {
        public static contentType = 'media';
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

        public static videoProviderVimeo:string = 'vimeo';
        public static videoProviderYoutube:string = 'youtube';

        public static videoProviders:IVideoProvider[] = [
            {
                providerKey: Media.videoProviderVimeo,
                validationRegex: /^[0-9]{9}$/,
                idLength: 9
            },
            {
                providerKey: Media.videoProviderYoutube,
                validationRegex: /^[A-Za-z0-9_-]{11}$/,
                idLength: 11
            }
        ];

        public static mediaTypeImage:string = 'image';
        public static mediaTypeVideo:string = 'video';
        public static mediaTypes:string[] = [Media.mediaTypeImage, Media.mediaTypeVideo];

        public media:(IImageContent|IVideoContent)[] = [];
        public size:string;
        public alignment:string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



