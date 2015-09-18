namespace common.filters.trust {

    describe('Trust filter', function () {
        let $filter:ng.IFilterService;

        beforeEach(function () {
            module('app');

            inject(function (_$filter_) {
                $filter = _$filter_;
            });
        });

        it('should trust potentially unsafe html', function () {

            let unsafe = `<span onmouseover="this.textContent=&quot;Explicitly trusted HTML bypasses sanitization.&quot;">Hover over this text.</span>`;

            let result = $filter('trust')(unsafe, 'html');

            expect(result).to.be.instanceOf(Object);
        });
    });

}