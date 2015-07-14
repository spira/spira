module common.services {

    export const namespace = 'common.services';

    export interface IUserService {
        getAllUsers():ng.IPromise<Object>;
    }

    class UserService {

        static $inject:string[] = ['ngRestAdapter'];
        constructor(private ngRestAdapter: NgRestAdapter.INgRestAdapterService) {

        }

        public getAllUsers(){

            return this.ngRestAdapter.get('/users')
                .then((res) => {
                    return res.data;
                })
                .catch((err) => {
                    console.error(err);
                })
            ;

        }

    }

    angular.module(namespace+'.userService', [])
        .service('userService', UserService);

}



