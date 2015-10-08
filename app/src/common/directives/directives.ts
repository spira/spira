namespace common.directives {

    export const namespace = 'common.directives';

    angular.module(namespace, [
        namespace + '.penEditor',
        namespace + '.menuToggle',
        namespace + '.contentSectionsInput',
    ])
    ;


}