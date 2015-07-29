module common.providers {

    export const namespace = 'common.providers';

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

        public getChildStates = (stateName:string):global.IState[] => {

            let state:global.IState = <global.IState>this.$state.get(stateName);

            var routeName = state.name;

            // this regex is going to filter only direct children of this route.
            var childRouteRegex = new RegExp(routeName + "\.[a-z]+$", "i");
            var states = <global.IState[]>this.$state.get();

            return _.filter(states, function(state) {
                return childRouteRegex.test(state.name) && !state.abstract;
            });

        }

    }

    angular.module(namespace + '.stateHelperServiceProvider', [])
        .provider('stateHelperService', StateHelperServiceProvider)
    ;


}
