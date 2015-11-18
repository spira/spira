namespace common.directives.contentSectionsInput {

    export const namespace = 'common.directives.contentSectionsInput';

    angular.module(namespace, [
        namespace + '.set',
        namespace + '.item',
        namespace + '.sectionInputMedia',
        namespace + '.sectionInputPromo',
        namespace + '.sectionInputRichText',
        namespace + '.sectionInputBlockquote',
    ]);


}