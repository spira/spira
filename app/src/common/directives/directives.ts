namespace common.directives {

    export const namespace = 'common.directives';

    angular.module(namespace, [
        namespace + '.menuToggle',
        namespace + '.uploadImage',
        namespace + '.markdownEditor',
        namespace + '.selectMediaImage',
        namespace + '.contentSectionsInput',
    ])
    ;


}