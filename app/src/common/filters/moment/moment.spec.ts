namespace common.filters.momentFilters {

    describe('Moment filter', function () {
        let $filter:ng.IFilterService;

        beforeEach(function () {
            module('app');

            inject(function (_$filter_) {
                $filter = _$filter_;
            });
        });

        it('should filter a moment object to a friendly date', function () {

            let dt = moment().subtract(10, 'minutes');

            let result = $filter('fromNow')(dt);

            expect(result).to.equal('10 minutes ago');
        });
    });

}