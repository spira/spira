module common.services {

    export class StateHelperServiceProvider implements ng.IServiceProvider {

        private states = [];

        constructor() {}

        public getStates = function() {
            return this.states;
        };

        public addState = function (name, options) {
            this.states.unshift({
                name: name,
                options: options
            });
        };

        public $get = ['$state', function StateHelperServiceFactory($state) {
            return new StateHelperService($state);
        }];

    }

    export class StateHelperService {

        constructor(private $state:ng.ui.IStateService) {}

        private getChildStates = (stateName:string) => {

            let state:ng.ui.IState = this.$state.get(stateName);

            var routeName = state.name;

            // this regex is going to filter only direct children of this route.
            var childRouteRegex = new RegExp(routeName + "\.[a-z]+$", "i");
            var states = this.$state.get();

            return _.filter(states, function(state) {
                return childRouteRegex.test(state.name) && !state.abstract;
            });

        }

    }

    angular.module('stateHelperServiceProvider', [])
        .provider('stateHelperService', StateHelperServiceProvider)
    ;


}
