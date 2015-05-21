angular.module('stateHelperServiceProvider', [])
    .provider('stateHelperService', function () {
        var states = [];


        this.$get = function ($state) {

            return {

                getChildStates : function(state){

                    if (_.isString(state)){
                        state = $state.get(state);
                    }

                    var routeName = state.name;

                    // this regex is going to filter only direct childs of this route.
                    var childRouteRegex = new RegExp(routeName + "\.[a-z]+$", "i");
                    var states = $state.get();

                    return _.filter(states, function(state) {
                        return childRouteRegex.test(state.name) && !state.abstract;
                    });

                }

            };

        };

        this.getStates = function() {
            return states;
        };

        this.addState = function (name, options) {
            states.unshift({
                name: name,
                options: options
            });
        };

    });