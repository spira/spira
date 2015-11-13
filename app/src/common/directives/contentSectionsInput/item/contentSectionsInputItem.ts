namespace common.directives.contentSectionsInput.item {

    export const namespace = 'common.directives.contentSectionsInput.item';

    export interface ISettingsControllerBindings{
        templateUrl: string;
        controller: Object;
        controllerAs: string;
    }

    export class ContentSectionsInputItemController {

        public toolbarOpen = false;
        public section:common.models.Section<any>;
        private childControllerSettings:ISettingsControllerBindings = null;
        public $element:ng.IAugmentedJQuery|JQuery;
        public parentSetController:set.ContentSectionsInputSetController;

        static $inject = ['ngRestAdapter', '$mdDialog', '$mdBottomSheet', '$q'];
        constructor(private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
                    private $mdDialog:ng.material.IDialogService,
                    private $mdBottomSheet:ng.material.IBottomSheetService,
                    private $q:ng.IQService
        ){

        }

        public registerSettingsBindings(bindingSettings:ISettingsControllerBindings){

            this.childControllerSettings = bindingSettings;
        }

        /**
         * Toggle the settings pane.
         * @todo Note that we are only popping an empty (dummy) bottom sheet because the md-fab-toolbar
         * does not have capabilities to lock it open while the bottom sheet is open. Instead we are partially replicating
         * the functionality of fab-toolbar and using the event bound to bottomSheet opening to prompt the toolbar to
         * close when we click away. This should be refactored to use fab-toolbar when https://github.com/angular/material/issues/4973
         * is fixed.
         * @param $event
         */
        public toggleSettings($event:MouseEvent):ng.IPromise<any>{

            this.toolbarOpen = !this.toolbarOpen;

            if (this.toolbarOpen){

                let bottomSheetConfig:ng.material.IBottomSheetOptions = {
                    templateUrl: 'templates/common/directives/contentSectionsInput/item/dummySettingsMenu.tpl.html',
                    parent: jQuery(this.$element).find('.section-input'),
                    targetEvent: $event,
                    disableParentScroll: false,
                };

                if (this.childControllerSettings){
                    bottomSheetConfig = _.merge(bottomSheetConfig, {
                        templateUrl: this.childControllerSettings.templateUrl,
                        controller: SettingsSheetController,
                        controllerAs: this.childControllerSettings.controllerAs,
                        locals: {
                            controllerBinding: this.childControllerSettings.controller,
                        }
                    });
                }

                return this.$mdBottomSheet.show(bottomSheetConfig).finally(() => {
                    this.toolbarOpen = false;
                })
            } else {
                this.$mdBottomSheet.cancel();
            }

        }

    }


    class SettingsSheetController {


        static $inject = ['controllerBinding'];
        constructor(controllerBinding) {
            this.bindController(controllerBinding);
        }

        /**
         * Iterate through the properties of the injected controller, binding to this controller.
         * This feels hacky and there is probably a better way to do this
         * @param controllerBinding
         */
        private bindController(controllerBinding) {
            _.transform(controllerBinding, (thisController, value, key) => {

                thisController[key] = value;

            }, this);
        }


    }

    class ContentSectionsInputItemDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['contentSectionsInputItem', '^contentSectionsInputSet'];
        public templateUrl = 'templates/common/directives/contentSectionsInput/item/contentSectionsInputItem.tpl.html';
        public replace = false;
        public scope = {
            section: '=',
        };

        public controllerAs = 'ContentSectionsInputItemController';
        public controller = ContentSectionsInputItemController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ContentSectionsInputItemController, set.ContentSectionsInputSetController]) => {

            let thisController = $controllers[0];
            let parentSetController = $controllers[1];

            thisController.parentSetController = parentSetController;

            thisController.$element = $element;

        };

        static factory(): ng.IDirectiveFactory {
            return () => new ContentSectionsInputItemDirective();
        }
    }

    angular.module(namespace, [])
        .directive('contentSectionsInputItem', ContentSectionsInputItemDirective.factory())
    ;


}