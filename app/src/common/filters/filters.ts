namespace common.filters {

    export const namespace = 'common.filters';

    angular.module(namespace, [
        namespace + '.trust',
        namespace + '.stringFilters',
        namespace + '.momentFilters',
    ]);


}