namespace app.head {

    export const namespace = 'app.head';

    export class HeadController {

        static $inject = ['$state'];

        constructor(public $state:ng.ui.IStateService) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', HeadController);

}