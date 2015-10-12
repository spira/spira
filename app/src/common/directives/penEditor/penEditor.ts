namespace common.directives.penEditor {

    export const namespace = 'common.directives.penEditor';

    class PenEditorDirective implements ng.IDirective {

        public restrict = 'E';
        public require = 'ngModel';
        public templateUrl = 'templates/common/directives/penEditor/penEditor.tpl.html';
        public replace = true;
        public scope = {
        };

        constructor(private marked: any) {
        }

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $ngModelController: ng.INgModelController) => {

            let pen:Pen.Pen;

            $ngModelController.$render = () => {

                let htmlContent = $ngModelController.$modelValue? this.marked($ngModelController.$modelValue) : 'Your content here';
                let htmlElement = $element.html(`<div>${htmlContent}</div>`)[0];

                if (!pen){

                    pen = new Pen({
                        editor: htmlElement,
                        stay: false,
                    });

                    htmlElement.addEventListener('input', () => {
                        $ngModelController.$setViewValue(pen.toMd());
                    }, false);

                }

                $ngModelController.$setValidity('foo', true);
            };


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