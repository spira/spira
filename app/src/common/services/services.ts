namespace common.services {

    export const namespace = 'common.services';

    angular.module(namespace, [
        namespace+'.tag',
        namespace+'.auth',
        namespace+'.user',
        namespace+'.image',
        namespace+'.article',
        namespace+'.countries',
        namespace+'.timezones',
        namespace+'.pagination',
        namespace+'.notification',
    ]);

}

