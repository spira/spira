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

        it('should get the uuid identifier when there is no permalink', () => {

            let uuid = seededChance.guid();

            let article = new common.models.Article({articleId:uuid});

            expect(article.getIdentifier()).to.be.equal(uuid);

        });

        it('should get the permalink identifier when there is a permalink', () => {

            let uuid = seededChance.guid();
            let permalink = seededChance.string();

            let article = new common.models.Article({articleId:uuid, permalink:permalink});

            expect(article.getIdentifier()).to.be.equal(permalink);

        });

    });

})();