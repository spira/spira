/**
 * @todo consider removing this filter, it is not used at the moment - keeping it to demonstrate pattern for registering
 * filters with injectable dependencies
 */
namespace common.filters.trust {

    export const namespace = 'common.filters.trust';

    export function TrustFilter($sce:ng.ISCEService) {

        return function trust(text:string, type:string){
            return $sce.trustAs(type || 'html', text);
        }
    }

    TrustFilter.$inject = ['$sce'];

    angular.module(namespace, [])
        .filter('trust', TrustFilter)
    ;


}