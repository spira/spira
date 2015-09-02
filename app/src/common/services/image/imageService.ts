namespace common.services.image {

    export const namespace = 'common.services.image';

    export interface IUploadOptions {
        files: File[]; //override to any as the api actually accepts File|File[] but typescript doesn't allow union types
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
         * @param inputOptions
         */
        public upload(inputOptions:IUploadOptions):ng.IPromise<common.models.Image> {

            let cloudinaryOptions = this.getCloudinaryUploadConfig(inputOptions);

            let imageDeferred = this.$q.defer();

            this.ngFileUpload.upload(cloudinaryOptions)
                .progress(function (evt:any) {
                    var progressPercentage = (<any>_).round(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ' + evt.config.file.name);
                    imageDeferred.notify(progressPercentage);
                }).success(function (data:any, status:any, headers:any, config:any) {
                    console.log('file ' + config.file.name + 'uploaded. Response: ' + data);

                    let addImagePromise = this.addImage(cloudinaryOptions, data);
                    imageDeferred.resolve(addImagePromise); //@todo verify that resolving a promise chains properly
                }).error(function (data, status, headers, config) {
                    console.log('error status: ' + status);
                });

            return imageDeferred.promise; //return the original promise so the progress runs normally
        }

        /**
         * Get the upload configuration defaults for the cdn
         * @returns {{url: string}}
         */
        private getCloudinaryUploadConfig(inputOptions:IUploadOptions):ng.angularFileUpload.IFileUploadConfig {

            let cloudinaryOptions:ng.angularFileUpload.IFileUploadConfig = {
                file: <any>inputOptions.files,
                url: 'server/upload/url',
                method: 'post',
            };

            return cloudinaryOptions;
        }

        /**
         * Link the cdn image to the api
         * @param uploadOptions
         * @param response
         * @returns {IPromise<common.models.Image>}
         */
        private addImage(uploadOptions:ng.angularFileUpload.IFileUploadConfig, response:ng.IHttpPromiseCallbackArg<any>):ng.IPromise<common.models.Image> {

            let imageModel = this.newImage();
            imageModel.publicId = null; //@todo work out how to pull this from the cloudinary response.

            return this.ngRestAdapter.put('/images/'+imageModel.imageId, imageModel.getAttributes()).then(() => {
                imageModel.setExists(true);
                return imageModel;
            });

        }
    }

    angular.module(namespace, [])
        .service('imageService', ImageService);

}



