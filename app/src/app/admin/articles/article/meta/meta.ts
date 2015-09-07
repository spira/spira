namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        public metas:common.models.ArticleMeta[];

        static $inject = ['article', 'articleService'];

        constructor(public article:common.models.Article, private articleService:common.services.article.ArticleService) {
        }

        /**
         * Add a new meta tag to article.
         */
        public add():void {

            this.article._articleMeta.push(
                new common.models.ArticleMeta({
                    metaName:'',
                    metaContent:'',
                    metaProperty:''
                })
            );

        }
    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}