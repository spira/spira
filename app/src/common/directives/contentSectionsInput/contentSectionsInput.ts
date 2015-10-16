namespace common.directives.contentSectionsInput {

    export const namespace = 'common.directives.contentSectionsInput';

    interface IContentSectionsInputScope extends ng.IScope{
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

    export class ContentSectionsInputController {

        private sectionTypes: ISectionTypeMap;
        public sections:common.models.Section<any>[];
        private onSectionUpdate:ISectionUpdateCallback;

        static $inject = ['ngRestAdapter', '$mdDialog'];
        constructor(private ngRestAdapter:NgRestAdapter.NgRestAdapterService, private $mdDialog:ng.material.IDialogService){
            this.sectionTypes = {
                [common.models.sections.RichText.contentType] : {
                    name: "Rich Text",
                    icon: 'format_align_left',
                },
                [common.models.sections.Blockquote.contentType] : {
                    name: "Blockquote",
                    icon: 'format_quote',
                },
                [common.models.sections.Image.contentType] : {
                    name: "Image",
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

    class ContentSectionsInputDirective implements ng.IDirective {

        public restrict = 'E';
        public require =  'ngModel';
        public templateUrl = 'templates/common/directives/contentSectionsInput/contentSectionsInput.tpl.html';
        public replace = true;
        public scope = {
            sections: '=ngModel',
            onSectionUpdate: '&?',
        };


        public controllerAs = 'ContentSectionsInputController';
        public controller = ContentSectionsInputController;
        public bindToController = true;

        constructor() {
        }

        public link = ($scope: IContentSectionsInputScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $ngModelController: ng.INgModelController) => {


        };

        static factory(): ng.IDirectiveFactory {
            return () => new ContentSectionsInputDirective();
        }
    }

    angular.module(namespace, [
        namespace + '.sectionInputImage',
        namespace + '.sectionInputPromo',
        namespace + '.sectionInputRichText',
        namespace + '.sectionInputBlockquote',
    ])
        .directive('contentSectionsInput', ContentSectionsInputDirective.factory())
    ;


}