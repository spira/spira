namespace common.filters.momentFilters {

    export const namespace = 'common.filters.momentFilters';

    export function FromNowFilter() {

        return function fromNow(date:moment.Moment):string|any{
            if(!moment.isMoment(date)){
                return date;
            }
            return date.fromNow();
        }
    }

    angular.module(namespace, [])
        .filter('fromNow', FromNowFilter)
    ;


}