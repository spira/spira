namespace common.services {

    export const namespace = 'common.services';

    angular.module(namespace, [
        namespace+'.tag',
        namespace+'.auth',
        namespace+'.user',
        namespace+'.error',
        namespace+'.image',
        namespace+'.region',
        namespace+'.article',
        namespace+'.countries',
        namespace+'.timezones',
        namespace+'.pagination',
        namespace+'.notification',
    ]);

}

