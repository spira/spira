module app.partials.navigation{

    export const namespace = 'app.partials.navigation';

    class NavigationController {

        static $inject = ['$scope', 'stateHelperService', '$window'];
        constructor(private $scope, private stateHelperService, private $window) {

            var childStates = stateHelperService.getChildStates(app.guest.namespace);

            //using the state.data.sortAfter key build a topology and sort it
            var sortMap = _.reduce(childStates, function(t, state:ng.ui.IState){
                t.add(state.name, _.get(state, 'data.sortAfter', []));
                return t;
            }, new $window.Toposort()).sort();

            $scope.navigationStates = _.chain(sortMap)
                .map(function(stateName){
                    return _.find(childStates, {name: stateName}); //find the state by name
                })
                .filter(function(state){
                    return _.get(state, 'data.navigation', false); //only return those that are marked as navigation
                })
                .reverse() //reverse the array
                .value()
            ;

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', NavigationController);


}