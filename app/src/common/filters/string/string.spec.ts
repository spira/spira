namespace common.filters.stringFilters {

    describe('String filters', function () {
        let $filter:ng.IFilterService;

        beforeEach(function () {
            module('app');

            inject(function (_$filter_) {
                $filter = _$filter_;
            });
        });

        it('should filter camel cased string to human readable', function () {

            let result = $filter('fromCamel')("camelCase");

            expect(result).to.equal('Camel Case');
        });
    });

}