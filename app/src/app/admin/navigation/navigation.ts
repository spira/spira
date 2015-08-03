module app.admin.navigation{

    export const namespace = 'app.admin.navigation';

    class AdminNavigationController extends app.abstract.navigation.AbstractNavigationController {

        static $inject = ['stateHelperService', '$window', 'ngJwtAuthService', '$state'];
        constructor(
            stateHelperService:common.providers.StateHelperService,
            $window:global.IWindowService,
            ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            $state:ng.ui.IStateService
        ) {

            super(stateHelperService, $window, ngJwtAuthService, $state);

        }

        protected getNavigationStates():global.IState[]{

            let childStates = this.stateHelperService.getChildStates(app.admin.namespace, 1);

            console.log(childStates);

            return this.getNavigableStates(childStates);
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', AdminNavigationController);


}