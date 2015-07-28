module app.partials.navigation{

    export const namespace = 'app.partials.navigation';

    export interface IScope extends ng.IScope
    {
        navigationStates:ng.ui.IState[];
        authService:NgJwtAuth.NgJwtAuthService;
        promptLogin():any;
        logout():any;
    }

    class NavigationController {

        static $inject = ['$scope', 'stateHelperService', '$window', 'ngJwtAuthService', '$state'];
        constructor(
            private $scope:IScope,
            private stateHelperService:common.providers.StateHelperService,
            private $window:global.IWindowService,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private $state:ng.ui.IStateService
        ) {

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


            $scope.authService = ngJwtAuthService;
            $scope.promptLogin = () => ngJwtAuthService.promptLogin();
            $scope.logout = () => {
                ngJwtAuthService.logout();
                let currentState:global.IState = <global.IState>this.$state.current;
                if (currentState.name && currentState.data.loggedIn) {
                    this.$state.go('app.guest.home'); //go back to the homepage if we are currently in a logged in state
                }
            }

        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', NavigationController);


}