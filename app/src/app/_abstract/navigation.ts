namespace app.abstract.navigation {

    export const namespace = 'app.abstract.navigation';

    export class AbstractNavigationController {

        public navigableStates:global.IState[] = [];
        public navigableStateIndex:number;

        static $inject = ['stateHelperService', '$window', 'ngJwtAuthService', '$state', '$rootScope'];
        public loggedInUser:common.models.User;

        constructor(protected stateHelperService:common.providers.StateHelperService,
                    private $window:global.IWindowService,
                    protected ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    public $state:ng.ui.IStateService,
                    private $rootScope:ng.IRootScopeService) {

            this.navigableStates = this.getNavigationStates();
            this.loggedInUser = <common.models.User>(<any>ngJwtAuthService).user;

            this.defineNavigableStateIndex();

            this.$rootScope.$on('navigation', (event:ng.IAngularEvent, message) => {

                if (message == 'recalculateNavigableStateIndex'){
                    this.defineNavigableStateIndex();
                }
            });

        }

        /**
         * Set the value for the current index of the navigation state
         */
        public defineNavigableStateIndex():void {
            this.navigableStateIndex = _.findIndex(this.navigableStates, (state) => {
                return this.$state.current.name == state.name;
            });
        }

        /**
         * Get all the navigation states
         * @returns {global.IState[]}
         */
        protected getNavigationStates():global.IState[] {

            let childStates = <global.IState[]>this.stateHelperService.getChildStates(app.guest.namespace);

            return this.getNavigableStates(childStates);

        }

        /**
         * Sort the states based on their `sortAfter` attribute
         * @param states
         * @returns {any}
         */
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

        /**
         * Filter the states that are navigable
         * @param states
         * @returns {global.IState[]}
         */
        protected getNavigableStates(states:global.IState[]):global.IState[] {

            let navigable = _.chain(states)
                    .filter(function (state) {
                        return _.get(state, 'data.navigation', false); //only return those that are marked as navigation
                    })
                    .value()
                ;

            return this.sortNavigationStates(navigable);
        }

    }

}