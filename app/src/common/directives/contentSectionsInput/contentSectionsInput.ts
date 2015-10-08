namespace common.directives.contentSectionsInput {

    export const namespace = 'common.directives.contentSectionsInput';

    interface IContentSectionsInputScope extends ng.IScope{
        ngModel():common.models.Section[];
    }

    interface ISectionType {
        name: string,
        icon: string,
    }

    interface ISectionTypeMap {
        [key:string]: ISectionType;
    }

    class ContentSectionsInputController {

        public sectionTypes: ISectionTypeMap;
        public sections:common.models.Section[];

        static $inject = ['ngRestAdapter'];
        constructor(private ngRestAdapter:NgRestAdapter.NgRestAdapterService){
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
                }
            };

            if (!this.sections){
                this.sections = [];
            }
        }

        public addSectionType(sectionTypeKey:string):void{
            this.sections.push(new common.models.Section({
                sectionId: this.ngRestAdapter.uuid(),
                type: sectionTypeKey,
            }));
        }

        public removeSection(section:common.models.Section):void{

            this.sections = _.without(this.sections, section);
        }

        public moveSection(section:common.models.Section, moveUp:boolean = true):void{

            let sectionIndex:number = _.findIndex(this.sections, section);
            let swapIndex:number = sectionIndex;

            if(moveUp){
                swapIndex --;
            }else{
                swapIndex++;
            }

            this.sections[sectionIndex] = this.sections[swapIndex];
            this.sections[swapIndex] = section;
        }

    }

    class ContentSectionsInputDirective implements ng.IDirective {

        public restrict = 'E';
        public require =  'ngModel';
        public templateUrl = 'templates/common/directives/contentSectionsInput/contentSectionsInput.tpl.html';
        public replace = true;
        public scope = {
            sections: '=ngModel',
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
        namespace + '.sectionInputRichText',
        namespace + '.sectionInputBlockquote',
    ])
        .directive('contentSectionsInput', ContentSectionsInputDirective.factory())
    ;


}