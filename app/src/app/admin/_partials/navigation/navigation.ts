module app.admin.partials.navigation{

    export const namespace = 'app.admin.partials.navigation';

    class AdminNavigationController extends app.partials.navigation.NavigationController {

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

            let childStates = this.stateHelperService.getChildStates(app.admin.namespace);

            return this.getNavigableStates(childStates);
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', AdminNavigationController);


}