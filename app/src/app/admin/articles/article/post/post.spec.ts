namespace app.admin.articles.article.post {

    describe('Post Editing', () => {

        let article:common.models.Article,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            PostController:PostController;

        beforeEach(() => {

            article = new common.models.Article({
                _tags: [
                    new common.models.Tag({
                        tag: 'existing'
                    }, true)
                ]
            }, true);

            module('app');

            inject(($controller, _$rootScope_, _tagService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();

                PostController = $controller(app.admin.articles.article.post.namespace + '.controller', {
                    article: article,
                    tagService: _tagService_,
                    $scope: $scope,
                });


            });


        });


        it('should be able to define tags as an array, and they should be transformed to a collection', () => {

            PostController.article = article;

            PostController.tags = ['newTag', 'anotherNewTag', 'existing'];

            PostController.updateArticleTags();

            expect(PostController.article._tag).to.have.lengthOf(3);

        });

    });

}