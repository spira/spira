namespace common.models.sections {

    describe('RichText Model', () => {

        it('should instantiate a new image', () => {

            let imageData = (new RichTextMock).getMockData();

            let image = new common.models.sections.RichText(imageData);

            expect(image).to.be.instanceOf(common.models.sections.RichText);

        });

        it('should mock a section rich text', () => {

            expect(RichTextMock.entity()).to.be.instanceOf(common.models.sections.RichText);
        });

    });

}