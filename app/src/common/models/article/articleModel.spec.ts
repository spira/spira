(() => {

    let seededChance = new Chance(1);

    describe('Article Model', () => {

        let title = seededChance.sentence(),
            articleId = seededChance.guid(),
            articleData = {
            articleId: articleId,
            title: title,
            permalink: title.toLowerCase().replace(' ', '-'),
            content:seededChance.paragraph({sentences: 10}),
            _articleMetas: [
                {
                    metaName: 'keyword',
                    metaContent: 'foo',
                    metaId: seededChance.guid(),
                    articleId: articleId
                },
                {
                    metaName: 'description',
                    metaContent: 'bar',
                    metaId: seededChance.guid(),
                    articleId: articleId
                },
                {
                    metaName: 'foobar',
                    metaContent: 'foobar',
                    metaId: seededChance.guid(),
                    articleId: articleId
                }
            ]
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

        it('should be able to hydrate the article metas', () => {

            let article = new common.models.Article(articleData);

            expect(_.size(article._articleMetas)).to.equal(5);

            // The first article meta is 'name' which is added via template
            expect(article._articleMetas[0].articleId).to.equal(article.articleId);

            expect(_.isEmpty(article._articleMetas[0].metaId)).to.be.false;

            let testableMetaTags = _.cloneDeep(article._articleMetas);
            _.forEach(testableMetaTags, (tag) => {
                delete(tag.metaId);
                delete(tag.articleId);
            });

            expect(testableMetaTags).to.deep.equal([
                {
                    metaName: 'name',
                    metaContent: ''
                },
                {
                    metaName: 'description',
                    metaContent: 'bar'
                },
                {
                    metaName: 'keyword',
                    metaContent: 'foo'
                },
                {
                    metaName: 'canonical',
                    metaContent: ''
                },
                {
                    metaName: 'foobar',
                    metaContent: 'foobar'
                }
            ]);

        });

    });

})();