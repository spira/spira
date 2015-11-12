namespace common.models.sections {

    describe('Media Model', () => {

        it('should instantiate a new media', () => {

            let mediaData = (new MediaMock).getMockData();

            let media = new common.models.sections.Media(mediaData);

            expect(media).to.be.instanceOf(common.models.sections.Media);

        });

        it('should mock a section media', () => {

            expect(MediaMock.entity()).to.be.instanceOf(common.models.sections.Media);
        });

    });

}