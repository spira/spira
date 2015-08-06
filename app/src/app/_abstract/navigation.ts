module app.abstract.navigation {

    export const namespace = 'app.abstract.navigation';

    export class AbstractNavigationController {

        public navigableStates:global.IState[] = [];

        static $inject = ['stateHelperService', '$window', 'ngJwtAuthService', '$state'];
        public loggedInUser:common.models.User;

        constructor(protected stateHelperService:common.providers.StateHelperService,
                    private $window:global.IWindowService,
                    protected ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    public $state:ng.ui.IStateService) {

            this.navigableStates = this.getNavigationStates();
            this.loggedInUser = <common.models.User>(<any>ngJwtAuthService).user;
        }

        protected getNavigationStates():global.IState[] {

            let childStates = <global.IState[]>this.stateHelperService.getChildStates(app.guest.namespace);

            return this.getNavigableStates(childStates);

        }

        protected sortNavigationStates(states:global.IState[]):global.IState[] {

            //using the state.data.sortAfter key build a topology and sort it
            var sortMap = _.reduce(states, function (t, state:ng.ui.IState) {
                t.add(state.name, _.get(state, 'data.sortAfter', []));
                return t;
            }, new this.$window.Toposort()).sort();

            return _.chain(sortMap)
                .map(function (stateName) {
                    return _.find(states, {name: stateName}); //find the state by name
                })
                .reverse() //reverse the array
                .value()
                ;

        }

        protected getNavigableStates(states:global.IState[]):global.IState[] {

            let navigable = _.chain(states)
                    .filter(function (state) {
                        return _.get(state, 'data.navigation', false); //only return those that are marked as navigation
                    })
                    .value()
                ;

            return this.sortNavigationStates(navigable);
        }

        public promptLogin():void {
            this.ngJwtAuthService.promptLogin();
        }

        public logout():void {
            this.ngJwtAuthService.logout();
            let currentState:global.IState = <global.IState>this.$state.current;
            if (currentState.name && currentState.data.loggedIn) {
                this.$state.go('app.guest.home'); //go back to the homepage if we are currently in a logged in state
            }
        }

    }

}