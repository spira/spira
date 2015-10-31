namespace app.root {

    export const namespace = 'app.root';

    export class RootController {

        static $inject = ['$state', 'authService'];

        constructor(
            public $state:ng.ui.IStateService,
            public authService:common.services.auth.AuthService
        ) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', RootController);

}