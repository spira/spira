namespace common.models.sections {

    describe('Blockquote Model', () => {

        it('should instantiate a new blockquote', () => {

            let blockquoteData = (new BlockquoteMock).getMockData();

            let blockquote = new common.models.sections.Blockquote(blockquoteData);

            expect(blockquote).to.be.instanceOf(common.models.sections.Blockquote);

        });

        it('should mock a section blockquote', () => {

            expect(BlockquoteMock.entity()).to.be.instanceOf(common.models.sections.Blockquote);
        });

    });

}