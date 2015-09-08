(() => {

    let seededChance = new Chance(1);

    describe('Tag Model', () => {

        let tagData = {
            tagId:seededChance.guid(),
            tag: seededChance.word(),
        };

        it('should instantiate a new tag', () => {

            let tag = new common.models.Tag(tagData);

            expect(tag).to.be.instanceOf(common.models.Tag);

        });

    });

})();