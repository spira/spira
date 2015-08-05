(() => {

    let seededChance = new Chance(1);

    describe('Article Model', () => {

        let title = seededChance.sentence();
        let articleData = {
            articleId:seededChance.guid(),
            title: title,
            permalink: title.toLowerCase().replace(' ', '-'),
            content:seededChance.paragraph({sentences: 10}),
        };

        it('should instantiate a new article', () => {

            let article = new common.models.Article(articleData);

            expect(article).to.be.instanceOf(common.models.Article);

        });

    });

})();