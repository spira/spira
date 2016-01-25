namespace common.directives.commandWidget {

    export const namespace = 'common.directives.commandWidget';

    export interface IInvalidControl {
        label: string;
        element: JQuery;
    }

    export class CommandWidgetController {


        public invalidControls:IInvalidControl[] = [];

        static $inject = ['$timeout'];

        constructor(
            private $timeout:ng.ITimeoutService
        ) {



        }

        public onSavePopover(){
            this.updateInvalidControls();
        }

        private updateInvalidControls() {

            this.invalidControls = _.reduce($('.ng-invalid:not(ng-form,[ng-form])'), (controls:IInvalidControl[], controlElement:JQuery) => {

                let elem = $(controlElement);

                let label:string = elem.siblings('label').text();
                if (!label){
                    label = elem.attr('placeholder');
                }

                let newControl:IInvalidControl = {
                    label: label,
                    element: controlElement,
                };

                controls.push(newControl);

                return controls;

            }, []);

        }

        /**
         * Scroll the user to the problem control element
         * @param control
         */
        public scrollToControl(control:IInvalidControl) {


            //try to find any parent tab frame(s) that are not selected
            let parentInactiveTabs = $(control.element).parents('md-tab-content:not(.md-active)');

            if (parentInactiveTabs.length > 0){
                //if found, defer navigation until digest cycle is completed
                this.$timeout(() => {

                    //iterate over all inactive parent tabs
                    _.each(parentInactiveTabs, (parentInactiveTab) => {
                        //find the index offset
                        let index:number = $(parentInactiveTab).index();
                        //find and initiate click event on the target tab
                        let targetTab = $(parentInactiveTab).closest('md-tabs').find('md-tab-item').eq(index);
                        targetTab.click();
                    });

                });
            }

            //scroll to the problematic control element
            $('html,body').animate({scrollTop: $(control.element).offset().top }, "slow");
        }

    }

    class CommandWidgetDirective implements ng.IDirective {

        public restrict = 'E';
        //public require =  ['CommandWidgetController'];
        public templateUrl = 'templates/common/directives/commandWidget/commandWidget.tpl.html';
        public replace = true;
        public scope = {
            saveAction: '&',
            saveDisabled: '=?',
            deleteAction: '&',
            deleteDisabled: '=?',
            cancelAction: '&?',
            cancelDisabled: '=?',
            formReference: '=?',
        };

        constructor(private $timeout: ng.ITimeoutService) {
        }

        public controllerAs = 'CommandWidgetController';
        public controller = CommandWidgetController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            let directive = ($timeout) => new CommandWidgetDirective($timeout);
            directive.$inject = ['$timeout'];
            return directive;
        }
    }

    angular.module(namespace, [])
        .directive('commandWidget', CommandWidgetDirective.factory())
    ;


}