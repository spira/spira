namespace common.models.sections {

    describe('Promo Model', () => {

        it('should instantiate a new promo', () => {

            let promoData = (new PromoMock).getMockData();

            let promo = new common.models.sections.Promo(promoData);

            expect(promo).to.be.instanceOf(common.models.sections.Promo);

        });

        it('should mock a section promo', () => {

            expect(PromoMock.entity()).to.be.instanceOf(common.models.sections.Promo);
        });

    });

}