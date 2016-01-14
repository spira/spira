namespace common.filters.stringFilters {

    export const namespace = 'common.filters.stringFilters';

    export function FromCamelFilter() {

        return function fromCamel(variableString:string):string {
            return _.capitalize(_.words(variableString).join(' '));
        }
    }

    angular.module(namespace, [])
        .filter('fromCamel', FromCamelFilter)
    ;


}