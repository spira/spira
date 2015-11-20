namespace common.models {

    let seededChance = new Chance();

    describe('Article Model', () => {

        let title = seededChance.sentence(),
            articleId = seededChance.guid(),
            articleData = {
                articleId: articleId,
                title: title,
                permalink: title.toLowerCase().replace(' ', '-'),
                content:seededChance.paragraph({sentences: 10}),
                _metas: [
                    {
                        metaName: 'keyword',
                        metaContent: 'foo',
                        metaId: seededChance.guid(),
                        metaableId: articleId
                    },
                    {
                        metaName: 'description',
                        metaContent: 'bar',
                        metaId: seededChance.guid(),
                        metaableId: articleId
                    },
                    {
                        metaName: 'foobar',
                        metaContent: 'foobar',
                        metaId: seededChance.guid(),
                        metaableId: articleId
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

            // The first article meta is 'name' which is added via template
            expect(article._metas[0].metaableId).to.equal(article.articleId);
            expect(_.isEmpty(article._metas[0].metaId)).to.be.false;

            let testableMetaTags = _.cloneDeep(article._metas);
            _.forEach(testableMetaTags, (tag) => {
                delete(tag.metaId);
                delete(tag.metaableId);
                delete(tag.metaableType);
            });

            expect(testableMetaTags).to.deep.equal([
                {
                    metaName: 'name',
                    metaContent: '',
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

        it('should hydrate nested sections when an new article is created', () => {

            let sections = common.models.SectionMock.collection(5);

            let article = common.models.ArticleMock.entity({
                _sections: sections
            });

            expect(article._sections).to.have.lengthOf(5);
            expect(article._sections[0]).to.be.instanceOf(common.models.Section);

            let expectedType = common.models.Section.getContentTypeMap()[article._sections[0].type];

            expect(article._sections[0].content).to.be.instanceOf(expectedType);


        });

        it('should sort the sections by the article sort order', () => {

            let sections = common.models.SectionMock.collection(3);

            let setOrder = [
                sections[1].sectionId,
                sections[0].sectionId,
                sections[2].sectionId,
            ];

            let article = common.models.ArticleMock.entity({
                _sections: sections,
                sectionsDisplay: {
                    sortOrder: setOrder
                }
            });


            expect(_.pluck(article._sections, 'sectionId')).to.deep.equal(setOrder);

        });

        it('should update the section sort order when prompted', () => {

            let sections = common.models.SectionMock.collection(3);

            let article = common.models.ArticleMock.entity({
                _sections: sections,
                sectionsDisplay: {
                    sortOrder: _.pluck(sections, 'sectionId')
                }
            });

            article._sections.pop();

            article.updateSectionsDisplay();

            expect(article._sections).to.have.length(2);
            expect(article.sectionsDisplay.sortOrder).to.have.length(2);
            expect(article.sectionsDisplay.sortOrder).to.deep.equal(_.pluck(article._sections, 'sectionId'));
            expect((<common.decorators.IChangeAwareDecorator>article).getChanged()).to.haveOwnProperty('sectionsDisplay');

        });

        it('should not update the section display property sort order when there are no sections', () => {

            let article = common.models.ArticleMock.entity({
                sectionsDisplay: {
                    sortOrder: []
                }
            });

            article.updateSectionsDisplay();

            expect(article._sections).to.be.empty;
            expect(article.sectionsDisplay.sortOrder).to.be.empty;
            expect((<common.decorators.IChangeAwareDecorator>article).getChanged()).to.be.empty;

        });


    });

}