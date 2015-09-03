namespace common.services.image {

    export const namespace = 'common.services.image';

    export interface IImageUploadOptions {
        file: File;
        alt:string;
        title?:string;
    }

    export interface ICloudinaryUpload {
        file: File;
        api_key: string;
        timestamp: number;
        signature: string;
        public_id?: string;
        resource_type?: string;
    }

    export interface ICloudinaryFileUploadConfig extends ng.angularFileUpload.IFileUploadConfig {
        fields: ICloudinaryUpload;
    }

    export interface IImageNotification {
        event: string;
        message: string;
        progressName?: string;
        progressValue?: any;
    }

    export interface IImageUploadPromise<T> extends ng.IPromise<T> {

    }

    export interface IImageDeferred<T> extends ng.IDeferred<T> {
        notify(state: IImageNotification): void;
        promise: IImageUploadPromise<T>;
    }



    export class ImageService {

        static $inject:string[] = ['Upload', '$q', 'ngRestAdapter'];

        constructor(private ngFileUpload:ng.angularFileUpload.IUploadService,
                    private $q:ng.IQService,
                    private ngRestAdapter:NgRestAdapter.INgRestAdapterService
        ) {


        }

        /**
         * Get an instance of the Image given data
         * @param data
         * @returns {common.models.Image}
         * @param exists
         */
        public static imageFactory(data:any, exists:boolean = false):common.models.Image {
            return new common.models.Image(data, exists);
        }

        /**
         * Get a new image with no values and a set uuid
         * @returns {common.models.Image}
         */
        public newImage():common.models.Image {

            return ImageService.imageFactory({
                imageId: this.ngRestAdapter.uuid(),
            });

        }

        /**
         *
         * @returns {IUploadPromise<T>}
         * @param imageUploadOptions
         */
        public uploadImage(imageUploadOptions:IImageUploadOptions):IImageUploadPromise<common.models.Image> {

            let cloudinaryOptions = this.getCloudinaryUploadConfig(imageUploadOptions);

            let deferredUploadProgress:IImageDeferred<common.models.Image> = this.$q.defer();

            let imageUploadPromise = this.signCloudinaryUpload(cloudinaryOptions, deferredUploadProgress)
                .then((signedCloudinaryOptions:ICloudinaryUpload) => this.uploadToCloudinary(signedCloudinaryOptions, deferredUploadProgress))
                .then(() => this.linkImageToApi(cloudinaryOptions, deferredUploadProgress))
            ;

            imageUploadPromise.then((image:common.models.Image) => {
                deferredUploadProgress.resolve(image);
            });

            return deferredUploadProgress.promise;
        }

        /**
         * Send the image config to cloudinary, and register it with the api
         * @param signedCloudinaryOptions
         * @returns {IUploadPromise<any>}
         * @param deferredUploadProgress
         */
        private uploadToCloudinary(signedCloudinaryOptions:ICloudinaryUpload, deferredUploadProgress:IImageDeferred<common.models.Image>):ng.angularFileUpload.IUploadPromise<ng.IHttpPromiseCallbackArg<any>> {

            let uploadOptions = this.getNgUploadConfig(signedCloudinaryOptions);

            return this.ngFileUpload.upload(uploadOptions)
                .progress(function (evt:any) {
                    var progressPercentage = (<any>_).round(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ' + evt.config.file.name);
                    deferredUploadProgress.notify({
                        event: 'cloudinary_upload',
                        message: "Uploading to cloudinary",
                        progressName: 'Upload progress',
                        progressValue: progressPercentage,
                    });
                });

        }

        /**
         * Get the upload configuration defaults for the cloudinary cdn
         * @returns {{url: string}}
         */
        private getCloudinaryUploadConfig(inputOptions:IImageUploadOptions):ICloudinaryUpload {

            return {
                file: inputOptions.file,
                api_key: undefined,
                signature: undefined,
                timestamp: moment().unix(),
                public_id: this.ngRestAdapter.uuid(),
                resource_type: 'image', // 'image', 'raw', 'auto'
                type: 'upload', //'upload', 'private', 'authenticated'.
            };

        }

        /**
         * Get the configuration object for the angular-file-upload service
         * @param cloudinaryOptions
         * @returns {{file: File, url: string, method: string, fields: Omitted}}
         */
        private getNgUploadConfig(cloudinaryOptions:ICloudinaryUpload):ICloudinaryFileUploadConfig {

            return {
                file: cloudinaryOptions.file,
                url: 'https://api.cloudinary.com/v1_1/demo/image/upload',
                method: 'POST',
                fields: <ICloudinaryUpload>_.omit(cloudinaryOptions, ['file'])
            };

        }

        /**
         * The parameters that must be signed in a cloudinary request
         * @type {string[]}
         */
        private cloudinarySignedParams = ['callback', 'eager', 'format', 'public_id', 'tags', 'timestamp', 'transformation', 'type'];

        /**
         * Sign the upload options using remote service which secures the api secret
         * @param uploadOptions
         * @returns {IPromise<ICloudinaryUpload>}
         * @param deferredUploadProgress
         */
        private signCloudinaryUpload(uploadOptions:ICloudinaryUpload, deferredUploadProgress:IImageDeferred<common.models.Image>):ng.IPromise<ICloudinaryUpload> {

            let signableString:string = _.chain(this.cloudinarySignedParams)
                    .filter((property) => {
                        return _.has(uploadOptions, property);
                    })
                    .map((property) => {
                        return property + '=' + uploadOptions[property];
                    })
                    .sort()
                    .value()
                    .join('&')
                ;

            deferredUploadProgress.notify({
                event: 'cloudinary_signature',
                message: "Signing request for cloudinary",
            });

            return this.ngRestAdapter.get('/cloudinary/signature?'+signableString)
                .then((res:any) => {

                    uploadOptions.signature = res.signature;
                    uploadOptions.api_key = res.api_key;

                    return uploadOptions;
                });

        }

        /**
         * Link the cdn image to the api
         * @param uploadOptions
         * @returns {IPromise<common.models.Image>}
         * @param deferredUploadProgress
         */
        private linkImageToApi(uploadOptions:ICloudinaryUpload, deferredUploadProgress:IImageDeferred<common.models.Image>):ng.IPromise<common.models.Image> {

            let imageModel = this.newImage();
            imageModel.publicId = uploadOptions.public_id;

            deferredUploadProgress.notify({
                event: 'api_link',
                message: "Uploading to API",
            });

            return this.ngRestAdapter.put('/images/'+imageModel.imageId, imageModel.getAttributes()).then(() => {
                imageModel.setExists(true);
                return imageModel;
            });

        }
    }

    angular.module(namespace, [])
        .service('imageService', ImageService);

}



