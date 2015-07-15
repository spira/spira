module common.services {

    export const namespace = 'common.services';

    export interface IUserService {
        getAllUsers():ng.IPromise<Object>;
    }

    class UserService {

        static $inject:string[] = ['ngRestAdapter', '$q'];
        constructor(private ngRestAdapter: NgRestAdapter.INgRestAdapterService, private $q:ng.IQService) {

        }

        public getAllUsers(){

            return this.ngRestAdapter.get('/users')
                .then((res) => {
                    return res.data;
                })
            ;

        }

    }

    angular.module(namespace+'.userService', [])
        .service('userService', UserService);

}



