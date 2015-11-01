namespace app.admin.articles.article.content {

    export const namespace = 'app.admin.articles.article.content';

    export class ContentController extends app.admin.AbstractContentController<common.models.Article, common.services.article.ArticleService> implements common.mixins.SectionableController {

        //SectionableController
        public sectionUpdated: (event:string, section:common.models.Section<any>) => void;

        static $inject = ['article', 'tagService', '$scope', 'articleService', 'groupTags'];
        constructor(
            public article:common.models.Article,
            protected tagService:common.services.tag.TagService,
            protected $scope:ng.IScope,
            protected articleService:common.services.article.ArticleService,
            public groupTags:common.models.Tag[]
        ) {
            super(article, tagService, $scope, articleService);
        }

    }


    angular.module(namespace, [])
        .controller(namespace+'.controller', ContentController);

}