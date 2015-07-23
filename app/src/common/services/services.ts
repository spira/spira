module common.services {

    export const namespace = 'common.services';

    angular.module(namespace, [
        namespace+'.user',
        namespace+'.countries',
    ]);

}



