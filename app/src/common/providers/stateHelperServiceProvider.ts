namespace common.providers {

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

        /**
         * Get the direct decendents of a state name. Optionally recurse decendents
         * @param stateName
         * @returns {*}
         * @param recurseLevel
         */
        public getChildStates = (stateName:string, recurseLevel:number = 0):global.IState[] => {

            // this regex is going to filter only direct children of this route.
            var childRouteRegex = new RegExp(stateName + "\.[a-z]+$", "i");

            let childStates =  _.filter(<global.IState[]>this.$state.get(), function(state) {
                return childRouteRegex.test(state.name);
            });

            if (!recurseLevel){
                return childStates;
            }

            recurseLevel --; //decrement the recursion

            return _.map(childStates, (state) => {
                state.children = this.getChildStates(state.name, recurseLevel); //recursively find the next child level
                return state;
            });

        }

    }

    angular.module(namespace + '.stateHelperServiceProvider', [])
        .provider('stateHelperService', StateHelperServiceProvider)
    ;


}
