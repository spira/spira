namespace common.directives.penEditor {

    export const namespace = 'common.directives.penEditor';

    class PenEditorDirective implements ng.IDirective {

        public restrict = 'E';
        public require = 'ngModel';
        public templateUrl = 'templates/common/directives/penEditor/penEditor.tpl.html';
        public replace = true;
        public scope = {
        };

        public pen:Pen.Pen;

        constructor(private marked: any) {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $ngModelController: ng.INgModelController) => {


            $ngModelController.$render = () => {

                let htmlContent = this.marked($ngModelController.$modelValue);
                let htmlElement = $element.html(`<div>${htmlContent}</div>`);

                if (!this.pen){

                    this.pen = new Pen({
                        editor: htmlElement[0]
                    });
                }

                $ngModelController.$setValidity('foo', true);
            };

            //@todo detect changes to the content (register events?) and commit the change by mutating $ngModelController.$modelValue


        };

        static factory(): ng.IDirectiveFactory {
            const directive =  (marked) => new PenEditorDirective(marked);
            directive.$inject = ['marked'];
            return directive;
        }
    }

    angular.module(namespace, [
    ])
        .directive('penEditor', PenEditorDirective.factory())
    ;


}