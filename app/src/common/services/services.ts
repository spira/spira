module common.services {

    export const namespace = 'common.services';

    angular.module(namespace, [
        namespace+'.auth',
        namespace+'.user',
        namespace+'.article',
        namespace+'.countries',
        namespace+'.timezones',
    ]);

}

