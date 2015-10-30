namespace common.directives.localizableInput {

    export const namespace = 'common.directives.localizableInput';

    export interface ILocalizationChangeHandler {
        (localizations:common.models.Localization[]):void;
    }

    export class LocalizableInputController {

        public localizableInput:common.models.Localization[];
        private changeHandler:ILocalizationChangeHandler;
        public localizationObject:common.models.Localization;

        static $inject = ['$mdDialog'];
        constructor(private $mdDialog) {
        }

        public registerChangeHandler(handler:ILocalizationChangeHandler){
            this.changeHandler = handler;
        }

        public promptAddLocalization($event:MouseEvent):ng.IPromise<string> {

            let dialogConfig:ng.material.IDialogOptions = {
                templateUrl: 'templates/common/directives/LocalizableInput/dialog/localizableInputDialog.tpl.html',
                controller: namespace+'.dialog.controller',
                controllerAs: 'LocalizableInputDialogController',
                clickOutsideToClose: true,
                locals: {}
            };

            return this.$mdDialog.show(dialogConfig)
                .then((localization:string) => {

                    //this.localizationObject = localization;
                    //
                    //if (this.changeHandler){
                    //    this.changeHandler(localization);
                    //}

                    return localization;
                });

        }
    }

    class LocalizableInputDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['ngModel','localizableInput'];
        public templateUrl = 'templates/common/directives/localizableInput/localizableInput.tpl.html';
        public replace = false;
        public scope = {
            localizableInput: '='
        };

        public controllerAs = 'LocalizableInputController';
        public controller = LocalizableInputController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ng.INgModelController, LocalizableInputController]) => {

            let $ngModelController = $controllers[0];
            let directiveController = $controllers[1];

            directiveController.registerChangeHandler((localizations:common.models.Localization[]) => {

                $ngModelController.$setDirty();
            });

            $ngModelController.$render = () => {
                //do something when the primary input changes?
            };

        };

        static factory(): ng.IDirectiveFactory {
            return () => new LocalizableInputDirective();
        }
    }

    angular.module(namespace, [
        'common.directives.localizableInput.dialog',
    ])
        .directive('localizableInput', LocalizableInputDirective.factory())
    ;


}