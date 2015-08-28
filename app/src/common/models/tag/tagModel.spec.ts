(() => {

    let seededChance = new Chance(1);

    describe('Tag Model', () => {

        let title = seededChance.sentence();
        let tagData = {
            tagId:seededChance.guid(),
            title: title,
            permalink: title.toLowerCase().replace(' ', '-'),
            content:seededChance.paragraph({sentences: 10}),
        };

        it('should instantiate a new tag', () => {

            let tag = new common.models.Tag(tagData);

            expect(tag).to.be.instanceOf(common.models.Tag);

        });

    });

})();