namespace common.directives.selectMediaImage {

    export const namespace = 'common.directives.selectMediaImage';

    interface IImageChangeHandler {
        (image:common.models.Image):void;
    }

    class SelectMediaImageController {

        private changeHandler:IImageChangeHandler;
        public currentImage:common.models.Image;

        static $inject = ['$mdDialog'];
        constructor(private $mdDialog) {
        }

        public registerChangeHandler(handler:IImageChangeHandler){
            this.changeHandler = handler;
        }

        public promptSelectImage():ng.IPromise<any> {

            let dialogConfig:ng.material.IDialogOptions = {
                templateUrl: 'templates/common/directives/selectMediaImage/dialog/selectMediaImageDialog.tpl.html',
                controller: namespace+'.dialog.controller',
                controllerAs: 'SelectMediaImageDialogController',
                clickOutsideToClose: true,
                locals: {}
            };

            return this.$mdDialog.show(dialogConfig)
                .then((image:common.models.Image) => {
                    this.currentImage = image;
                    if (this.changeHandler){
                        this.changeHandler(image);
                    }
                });

        }
    }

    class SelectMediaImageDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['ngModel','selectMediaImage'];
        public templateUrl = 'templates/common/directives/selectMediaImage/selectMediaImage.tpl.html';
        public replace = false;
        public scope = {
        };

        public controllerAs = 'SelectMediaImageController';
        public controller = SelectMediaImageController;
        public bindToController = true;

        constructor(private $mdDialog:ng.material.IDialogService) {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ng.INgModelController, SelectMediaImageController]) => {

            let $ngModelController = $controllers[0];
            let directiveController = $controllers[1];

            directiveController.registerChangeHandler((image:common.models.Image) => {
                $ngModelController.$setViewValue(image);

                $ngModelController.$setDirty();
            });

            $ngModelController.$render = () => {

                directiveController.currentImage = $ngModelController.$modelValue;
            };

        };

        static factory(): ng.IDirectiveFactory {
            const directive =  ($mdDialog) => new SelectMediaImageDirective($mdDialog);
            directive.$inject = ['$mdDialog'];
            return directive;
        }
    }

    angular.module(namespace, [
        'common.directives.selectMediaImage.dialog',
    ])
        .directive('selectMediaImage', SelectMediaImageDirective.factory())
    ;


}