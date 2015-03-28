angular.module('stateHelperServiceProvider', [])
    .provider('stateHelperService', function () {
        var states = [];

        this.$get = function () {
            return {};
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