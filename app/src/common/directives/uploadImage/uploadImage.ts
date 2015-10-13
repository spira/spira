namespace common.directives.uploadImage {

    export const namespace = 'common.directives.uploadImage';

    export interface IImageUploadedHandler {
        (image:common.models.Image):void;
    }

    export interface IProgressBar {
        statusText: string;
        visible: boolean;
        mode: string;
        value: number;
    }

    export interface IImageConstraints {
        maxHeight:number;
        minHeight:number;
        maxWidth:number;
        minWidth:number;
        maxSize:string;
        minSize:string;
    }

    //this is an almost complete copy from app/src/app/admin/media/media.ts. @todo refactor MediaController to use this directive and use that controllers spec to test this directive controller
    export class UploadImageController {

        static $inject = ['imageService'];

        public progressBar:IProgressBar = {
            statusText: 'Saving...',
            visible: false,
            mode: 'query',
            value: 0
        };

        public imageConstraints:IImageConstraints = {
            maxHeight:2500,
            minHeight:100,
            maxWidth:2500,
            minWidth:100,
            maxSize:'20MB',
            minSize:'10KB',
        };

        public queuedImage:common.services.image.IImageUploadOptions;
        public imageUploadForm:ng.IFormController;
        private imageUploadedHandler:IImageUploadedHandler;

        constructor(private imageService:common.services.image.ImageService) {

        }

        public registerImageUploadedHandler(handler:IImageUploadedHandler):void {
            this.imageUploadedHandler = handler;
        }

        /**
         * Upload files
         * @param image
         */
        public uploadImage(image:common.services.image.IImageUploadOptions):void {

            this.progressBar.visible = true;

            let onSuccess = (image:common.models.Image) => {

                this.progressBar.visible = false;


                if (this.imageUploadedHandler){
                    this.imageUploadedHandler(image);
                }

                this.queuedImage = null;
                this.imageUploadForm.$setPristine();

            };

            let onNotify = (notification:common.services.image.IImageNotification) => {

                this.progressBar.statusText = notification.message;
                switch (notification.event) {
                    case 'cloudinary_upload':
                        this.progressBar.mode = 'determinate';
                        this.progressBar.value = notification.progressValue;
                        break;
                    default:
                        this.progressBar.mode = 'indeterminate';
                }
            };

            this.imageService.uploadImage(image)
                .then(onSuccess, null, onNotify);
        }

    }

    class UploadImageDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['ngModel','uploadImage'];
        public templateUrl = 'templates/common/directives/uploadImage/uploadImage.tpl.html';
        public replace = false;
        public scope = {
        };

        public controllerAs = 'UploadImageController';
        public controller = UploadImageController;
        public bindToController = true;

        constructor(private $mdDialog:ng.material.IDialogService) {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ng.INgModelController, UploadImageController]) => {

            let $ngModelController = $controllers[0];
            let directiveController = $controllers[1];

            directiveController.registerImageUploadedHandler((image:common.models.Image) => {
                $ngModelController.$setViewValue(image);

                $ngModelController.$setDirty();
            });

            $ngModelController.$render = () => {

                //directiveController.currentImage = $ngModelController.$modelValue;
            };

        };

        static factory(): ng.IDirectiveFactory {
            const directive =  (imageService) => new UploadImageDirective(imageService);
            directive.$inject = ['imageService'];
            return directive;
        }
    }

    angular.module(namespace, [
    ])
        .directive('uploadImage', UploadImageDirective.factory())
    ;


}