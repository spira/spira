namespace common.models.sections {

    describe('Image Model', () => {

        it('should instantiate a new image', () => {

            let imageData = (new ImageMock).getMockData();

            let image = new common.models.sections.Image(imageData);

            expect(image).to.be.instanceOf(common.models.sections.Image);

        });

    });

}