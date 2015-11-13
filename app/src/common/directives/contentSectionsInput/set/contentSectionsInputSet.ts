namespace common.directives.contentSectionsInput.set {

    export const namespace = 'common.directives.contentSectionsInput.set';

    interface IContentSectionsInputSetScope extends ng.IScope{
        ngModel():common.models.Section<any>[];
    }

    interface ISectionType {
        name: string,
        icon: string,
    }

    interface ISectionTypeMap {
        [key:string]: ISectionType;
    }

    interface ISectionUpdateParams{
        event:string;
        section:common.models.Section<any>;
    }

    interface ISectionUpdateCallback{
        (paramObject: ISectionUpdateParams):void;
    }

    export interface ISettingsControllerBindings{
        templateUrl: string;
        controller: Object;
        controllerAs: string;
        element: ng.IAugmentedJQuery|JQuery;
    }

    export class ContentSectionsInputSetController {

        private sectionTypes: ISectionTypeMap;
        public sections:common.models.Section<any>[];
        private onSectionUpdate:ISectionUpdateCallback;
        private childControllerSettings:ISettingsControllerBindings = null;

        static $inject = ['ngRestAdapter', '$mdDialog', '$mdBottomSheet'];
        constructor(private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
                    private $mdDialog:ng.material.IDialogService,
                    private $mdBottomSheet:ng.material.IBottomSheetService
        ){
            this.sectionTypes = {
                [common.models.sections.RichText.contentType] : {
                    name: "Rich Text",
                    icon: 'format_align_left',
                },
                [common.models.sections.Blockquote.contentType] : {
                    name: "Blockquote",
                    icon: 'format_quote',
                },
                [common.models.sections.Media.contentType] : {
                    name: "Media",
                    icon: 'image',
                },
                [common.models.sections.Promo.contentType] : {
                    name: "Promo",
                    icon: 'announcement',
                }
            };

            if (!this.sections){
                this.sections = [];
            }
        }

        public addSectionType(sectionTypeKey:string):void{

            let section = new common.models.Section<any>({
                sectionId: this.ngRestAdapter.uuid(),
                type: sectionTypeKey,
            });

            this.sections.push(section);

            this.onSectionUpdate({
                event: 'added',
                section: section
            });
        }

        public removeSection(section:common.models.Section<any>):ng.IPromise<string>{

            var confirm = this.$mdDialog.confirm()
                .title("Are you sure you want to delete this section?")
                .content('This action <strong>cannot</strong> be undone')
                .ariaLabel("Confirm delete")
                .ok("Delete this section!")
                .cancel("Nope! Don't delete it.");

            return this.$mdDialog.show(confirm).then(() => {

                this.sections = _.without(this.sections, section);
                this.onSectionUpdate({
                    event: 'deleted',
                    section: section
                });

                return section.sectionId;
            });

        }

        public moveSection(section:common.models.Section<any>, moveUp:boolean = true):void{

            let sectionIndex:number = _.findIndex(this.sections, section);
            let swapIndex:number = sectionIndex;

            if(moveUp){
                swapIndex --;
            }else{
                swapIndex++;
            }

            this.sections[sectionIndex] = this.sections[swapIndex];
            this.sections[swapIndex] = section;
            this.onSectionUpdate({
                event: 'moved',
                section: section
            });
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

    class ContentSectionsInputSetDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['contentSectionsInputSet','ngModel'];
        public templateUrl = 'templates/common/directives/contentSectionsInput/set/contentSectionsInputSet.tpl.html';
        public replace = true;
        public scope = {
            sections: '=ngModel',
            onSectionUpdate: '&?',
        };


        public controllerAs = 'ContentSectionsInputSetController';
        public controller = ContentSectionsInputSetController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: IContentSectionsInputSetScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [ContentSectionsInputSetController, ng.INgModelController]) => {

            let thisController = $controllers[0];
            let $ngModelController = $controllers[1];

        };

        static factory(): ng.IDirectiveFactory {
            return () => new ContentSectionsInputSetDirective();
        }
    }

    angular.module(namespace, [])
        .directive('contentSectionsInputSet', ContentSectionsInputSetDirective.factory())
    ;


}