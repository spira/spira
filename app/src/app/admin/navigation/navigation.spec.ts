

describe('Admin Navigation', () => {

    let mockStateHelperService = {

        getChildStates : (parentNamespace:string, recursionDepth:number):global.IState[] => {

            return [
                {
                    name: 'firstState-navigable-admin',
                    data: {
                        navigation: true,
                        navigationGroup: 'admin',
                    },
                    children: [
                        {
                            name: 'firstStateChild-navigable',
                            data: {
                                navigation: true
                            }
                        },
                        {
                            name: 'firstStateChild-navigable',
                            data: {
                                navigation: false
                            }
                        }
                    ]
                },
                {
                    name: 'secondState-navigable',
                    data: {
                        navigation: true
                    }
                },
                {
                    name: 'thirdState-not-navigable',
                    data: {
                        navigation: false
                    }
                }

            ];

        }

    };


    let NavigationController:app.admin.navigation.AdminNavigationController
    ;

    beforeEach(() => {
        module('app');
    });

    beforeEach(()=> {

        sinon.spy(mockStateHelperService, 'getChildStates');

        inject(($controller, $rootScope, _ngJwtAuthService_, _stateHelperService_, _$window_, _$state_) => {
            NavigationController = $controller(app.admin.navigation.namespace+'.controller', {
                stateHelperService : mockStateHelperService,
                $window : _$window_,
                ngJwtAuthService : _ngJwtAuthService_,
                $state : _$state_,
            });

        });
    });

    afterEach(() => {
        (<any>mockStateHelperService.getChildStates).restore();
    });

    it('should be a valid controller', () => {

        expect(NavigationController).to.be.ok;
    });

    it('should have some navigation states', () => {

        expect(NavigationController.navigableStates).not.to.be.empty;

    });

    it('should get the navigable states from the state helper', () => {

        expect(mockStateHelperService.getChildStates).to.have.been.calledWith(app.admin.namespace, 1);

    });

    it('should have the only the states with navigation = true', () => {

        let states = NavigationController.navigableStates;

        expect(_.every(states, 'data.navigation')).to.be.true;
        expect(states).to.have.length(2);

    });

    it('should have the only the child states with navigation = true', () => {

        let states = NavigationController.navigableStates,
            childStates = _.find(states, 'children').children; //get first state with children

        expect(_.every(childStates, 'data.navigation')).to.be.true;
        expect(childStates).to.have.length(1);

    });

    it('should group the states by type', () => {

        let adminGroup = _.find(NavigationController.groupedNavigableStates, {key:'admin'});

        expect((<any>_).every(adminGroup.states, 'data.navigationGroup', 'admin')).to.be.true;

    });

});