namespace common.services {

    export const namespace = 'common.services';

    angular.module(namespace, [
        namespace+'.tag',
        namespace+'.auth',
        namespace+'.user',
        namespace+'.role',
        namespace+'.error',
        namespace+'.image',
        namespace+'.region',
        namespace+'.utility',
        namespace+'.article',
        namespace+'.countries',
        namespace+'.timezones',
        namespace+'.pagination',
        namespace+'.notification',
        namespace+'.systemInformation',
    ]);

}

