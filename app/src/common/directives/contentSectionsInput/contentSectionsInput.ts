namespace common.directives.contentSectionsInput {

    export const namespace = 'common.directives.contentSectionsInput';

    interface IContentSectionsInputScope extends ng.IScope{
        ngModel():common.models.Section[];
    }

    interface ISectionType {
        key: string,
        name: string,
        icon: string,
    }

    class ContentSectionsInputController {

        public sectionTypes: ISectionType[];
        public sections:common.models.Section[];

        constructor(){
            this.sectionTypes = [
                {
                    key: common.models.sections.RichText.contentType,
                    name: "Rich Text",
                    icon: 'format_align_left',
                },
                {
                    key: common.models.sections.Blockquote.contentType,
                    name: "Blockquote",
                    icon: 'format_quote',
                }
            ];

            if (!this.sections){
                this.sections = [];
            }
        }

        public addSectionType(sectionTypeKey:string){
            this.sections.push(new common.models.Section({
                type: sectionTypeKey,
            }));
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

    angular.module(namespace, [])
        .directive('contentSectionsInput', ContentSectionsInputDirective.factory())
    ;


}