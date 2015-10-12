namespace common.directives {

    export const namespace = 'common.directives';

    angular.module(namespace, [
        namespace + '.markdownEditor',
        namespace + '.menuToggle',
        namespace + '.contentSectionsInput',
    ])
    ;


}