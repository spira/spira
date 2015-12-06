namespace common.directives.markdownEditor {

    export const namespace = 'common.directives.markdownEditor';

    interface IMarkdownEditorDirectiveAttrs extends ng.IAttributes {
        spellChecker:string;
    }

    export interface IShortcodeOptions {
        slug:string;
    }

    class MarkdownEditorDirective implements ng.IDirective {

        public restrict = 'E';
        public require = 'ngModel';
        public templateUrl = 'templates/common/directives/markdownEditor/markdownEditor.tpl.html';
        public replace = false;

        constructor(private marked: any, private $mdDialog:ng.material.IDialogService) {
        }

        /**
         * This function is directly copied from app/bower_components/simplemde/src/js/simplemde.js
         * It is just not made available through the api for referencing
         * @param cm
         * @param active
         * @param startEnd
         * @private
         */
        private static _replaceSelection(cm, active, startEnd) {
            if(/editor-preview-active/.test(cm.getWrapperElement().lastChild.className))
                return;

            var text;
            var start = startEnd[0];
            var end = startEnd[1];
            var startPoint = cm.getCursor("start");
            var endPoint = cm.getCursor("end");
            if(active) {
                text = cm.getLine(startPoint.line);
                start = text.slice(0, startPoint.ch);
                end = text.slice(startPoint.ch);
                cm.replaceRange(start + end, {
                    line: startPoint.line,
                    ch: 0
                });
            } else {
                text = cm.getSelection();
                cm.replaceSelection(start + text + end);

                startPoint.ch += start.length;
                if(startPoint !== endPoint) {
                    endPoint.ch += start.length;
                }
            }
            cm.setSelection(startPoint, endPoint);
            cm.focus();
        }

        private injectShortCode(editor:SimpleMDE.SimpleMDE, shortcode:string, slug:string){
            MarkdownEditorDirective._replaceSelection(editor.codemirror, false, [`[`, `](${shortcode}:${slug})`]);
        }

        private shortcodeTool(model:models.IShortlinkableModelStatic):SimpleMDE.MDEToolAction {
            let shortcode = model.__shortcode;

            return (editor:SimpleMDE.SimpleMDE):void => {

                let dialogConfig:ng.material.IDialogOptions = {
                    templateUrl: 'templates/common/directives/markdownEditor/shortcodeDialog/shortcodeDialog.tpl.html',
                    controller: namespace+'.shortcodeDialog.controller',
                    controllerAs: 'ShortcodeDialogController',
                    clickOutsideToClose: true,
                    parent: '#admin-container',
                    locals: {
                        shortcodeType: shortcode
                    }
                };

                this.$mdDialog.show(dialogConfig)
                    .then((shortcodeOptions:IShortcodeOptions) => {

                        this.injectShortCode(editor, shortcode, shortcodeOptions.slug);
                    });

            };
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: IMarkdownEditorDirectiveAttrs, $ngModelController: ng.INgModelController):void => {

            let editor:SimpleMDE.SimpleMDE;

            let markdownParser = this.marked;

            let tools = {
                article: this.shortcodeTool(common.models.Article),
                recipe: this.shortcodeTool(common.models.Recipe),
            };

            $ngModelController.$render = () => {

                let markdownContent = $ngModelController.$modelValue;

                if (!editor){

                    editor = new SimpleMDE({
                        spellChecker: $attrs.spellChecker == 'true',
                        element: $element.find('textarea')[0],
                        previewRender: (plainText:string) => {
                            return markdownParser(plainText); // Returns HTML from a custom parser
                        },
                        toolbar: [
                            'bold', 'italic', 'heading-2', 'heading-3', 'quote', '|',
                            'unordered-list', 'ordered-list', '|',
                            'link', 'image', 'horizontal-rule', '|',
                            'preview', 'side-by-side', 'fullscreen',
                            '|',
                            //custom tools
                            {
                                name: "insert-article",
                                action: tools.article,
                                className: "fa fa-newspaper-o",
                                title: "Insert Article",
                            }
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
            const directive =  (marked, $mdDialog) => new MarkdownEditorDirective(marked, $mdDialog);
            directive.$inject = ['marked', '$mdDialog'];
            return directive;
        }
    }

    angular.module(namespace, [
        namespace + '.shortcodeDialog',
    ])
        .directive('markdownEditor', MarkdownEditorDirective.factory())
    ;


}