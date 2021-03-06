namespace app.admin.navigation {

    export const namespace = 'app.admin.navigation';

    export class AdminNavigationController extends app.abstract.navigation.AbstractNavigationController {

        public groupedNavigableStates = [
            {
                key: 'undefined',
                name: null,
                states: null
            },
            {
                key: 'cms',
                name: "Content Management",
                states: null,
                icon: 'folder',
            },
            {
                key: 'admin',
                name: "Administration",
                states: null,
                icon: 'settings',
            }
        ];

        public collapsed:boolean = false;

        static $inject = ['stateHelperService', '$window', 'ngJwtAuthService', '$state', '$rootScope'];

        constructor(stateHelperService:common.providers.StateHelperService,
                    $window:global.IWindowService,
                    ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    $state:ng.ui.IStateService,
                    $rootScope:ng.IRootScopeService) {

            super(stateHelperService, $window, ngJwtAuthService, $state, $rootScope);

            let groupedStates = _.groupBy(this.navigableStates, 'data.navigationGroup');

            this.groupedNavigableStates = _.map(this.groupedNavigableStates, (stateGroup) => {
                stateGroup.states = groupedStates[stateGroup.key];
                return stateGroup;
            });

        }

        protected getNavigationStates():global.IState[] {

            let childStates = this.stateHelperService.getChildStates(app.admin.namespace, 1);

            childStates = _.map(childStates, (state) => {
                if (state.children) {
                    state.children = _.compact(this.getNavigableStates(state.children));
                }
                return state;
            });

            return this.getNavigableStates(childStates);
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', AdminNavigationController);

}