module app.guest.navigation {

    export const namespace = 'app.guest.navigation';

    export class NavigationController extends app.abstract.navigation.AbstractNavigationController {


    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', NavigationController);

}