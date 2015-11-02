namespace common.directives.localizableInput {

    export const namespace = 'common.directives.localizableInput';

    export interface ILocalizationChangeHandler {
        (localizations:common.models.Localization<any>[]):void;
    }

    export interface IInputElementAttributes extends ng.IAttributes{
        ngModel: string;
    }

    interface ILocalizableInputScope extends ng.IScope{
        localizableInput: common.models.Localization<any>[];
    }

    export class LocalizableInputController {

        public localizableInput:common.models.Localization<any>[];
        private changeHandler:ILocalizationChangeHandler;
        private attributeKey:string;
        private inputNodeName:string;
        public $ngModelController:ng.INgModelController;

        static $inject = ['$mdDialog', '$compile'];
        constructor(private $mdDialog:ng.material.IDialogService, private $compile:ng.ICompileService) {
        }

        public registerChangeHandler(handler:ILocalizationChangeHandler){
            this.changeHandler = handler;
        }

        public getButtonElement($scope:ng.IScope):ng.IAugmentedJQuery{

            return this.$compile(`<md-button ng-click="LocalizableInputController.promptAddLocalization($event)" class="md-icon-button localizable-input"><md-icon>translate</md-icon></md-button>`)($scope);
        }

        /**
         * Get the attribute name by parsing the ng-model="path.to.attribute" attribute.
         * @todo consider alternative method as this relies on the attribute being at the top level of the localisation
         * @param $element
         * @param $attrs
         */
        public setInputAttributes($element:ng.IAugmentedJQuery, $attrs:IInputElementAttributes):void {
            this.attributeKey =  _.last($attrs.ngModel.split('.'));
            this.inputNodeName =  $element.prop('nodeName').toLowerCase();
        }

        /**
         * Prompt the localisation dialog to pop up
         * @param $event
         * @returns {IPromise<common.models.Localization<any>[]>}
         */
        public promptAddLocalization($event:MouseEvent):ng.IPromise<common.models.Localization<any>[]> {

            let dialogConfig:ng.material.IDialogOptions = {
                targetEvent: $event,
                templateUrl: 'templates/common/directives/localizableInput/dialog/localizableInputDialog.tpl.html',
                controller: namespace+'.dialog.controller',
                controllerAs: 'LocalizableInputDialogController',
                clickOutsideToClose: true,
                locals: {
                    localizations: this.localizableInput,
                    attributeKey: this.attributeKey,
                    inputNodeName: this.inputNodeName,
                    originalValue: this.$ngModelController.$modelValue,
                }
            };

            return this.$mdDialog.show(dialogConfig)
                .then((updatedLocalizations:common.models.Localization<any>[]) => {

                    this.changeHandler(updatedLocalizations);
                    this.localizableInput = updatedLocalizations;

                    return updatedLocalizations;
                });

        }
    }

    class LocalizableInputDirective implements ng.IDirective {

        public restrict = 'A';
        public require = ['ngModel','localizableInput'];
        public replace = false;
        public scope = {
            localizableInput: '='
        };

        public controllerAs = 'LocalizableInputController';
        public controller = LocalizableInputController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: ILocalizableInputScope, $element: ng.IAugmentedJQuery, $attrs: IInputElementAttributes, $controllers: [ng.INgModelController, LocalizableInputController]) => {

            let $ngModelController = $controllers[0];
            let directiveController = $controllers[1];

            directiveController.setInputAttributes($element, $attrs);

            $element.after(directiveController.getButtonElement($scope));

            let parent = $element.parent('md-input-container');
            if (parent.length){
                parent.addClass('localizable');
            }

            directiveController.registerChangeHandler((localizations:common.models.Localization<any>[]) => {
                $ngModelController.$setDirty();
            });

            directiveController.$ngModelController = $ngModelController;

        };

        static factory(): ng.IDirectiveFactory {
            return () => new LocalizableInputDirective();
        }
    }

    angular.module(namespace, [
        namespace + '.dialog',
    ])
        .directive('localizableInput', LocalizableInputDirective.factory())
    ;


}