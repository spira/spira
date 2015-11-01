namespace common.directives {

    export const namespace = 'common.directives';

    angular.module(namespace, [
        namespace + '.avatar',
        namespace + '.menuToggle',
        namespace + '.uploadImage',
        namespace + '.groupedTags',
        namespace + '.markdownEditor',
        namespace + '.localizableInput',
        namespace + '.selectMediaImage',
        namespace + '.contentSectionsInput',
    ])
    ;


}