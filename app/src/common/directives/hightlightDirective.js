angular.module('highlightDirective', [])
    .directive('highlight', function($interpolate){
        return {
            restrict: 'EA',
            scope: true,
            compile: function (tElem, tAttrs) {
                var interpolateFn = $interpolate(tElem.html(), true);
                tElem.html(''); // disable automatic intepolation bindings

                return function(scope, elem){
                    scope.$watch(interpolateFn, function (value) {
                        elem.html(hljs.highlightAuto(value).value);
                    });
                }
            }
        };
    });