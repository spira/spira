namespace common.directives.markdownEditor {

    export const namespace = 'common.directives.markdownEditor';

    interface IMarkdownEditorDirectiveScope extends ng.IScope{
        spellChecker:string;
    }

    class MarkdownEditorDirective implements ng.IDirective {

        public restrict = 'E';
        public require = 'ngModel';
        public templateUrl = 'templates/common/directives/markdownEditor/markdownEditor.tpl.html';
        public replace = false;
        public scope = {
            spellChecker : '@',
        };

        constructor(private marked: any) {
        }

        public link = ($scope: IMarkdownEditorDirectiveScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $ngModelController: ng.INgModelController) => {

            let editor:SimpleMDE.SimpleMDE;

            $ngModelController.$render = () => {

                let markdownContent = $ngModelController.$modelValue;

                if (!editor){

                    editor = new SimpleMDE({
                        spellChecker: $scope.spellChecker == 'true',
                        element: $element.find('textarea')[0],
                        toolbar: [
                            'bold', 'italic', 'heading-2', 'heading-3', 'quote', '|',
                            'unordered-list', 'ordered-list', '|',
                            'link', 'image', 'horizontal-rule', '|',
                            'preview', 'side-by-side', 'fullscreen',
                        ],
                    });

                    editor.value(markdownContent);

                    editor.codemirror.on('change', function(){
                        $ngModelController.$setViewValue(editor.value());
                        $ngModelController.$setDirty();
                    });

                }

            };


        };

        static factory(): ng.IDirectiveFactory {
            const directive =  (marked) => new MarkdownEditorDirective(marked);
            directive.$inject = ['marked'];
            return directive;
        }
    }

    angular.module(namespace, [
    ])
        .directive('markdownEditor', MarkdownEditorDirective.factory())
    ;


}