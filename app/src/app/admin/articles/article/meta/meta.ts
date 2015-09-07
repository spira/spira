namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        public metas:common.models.ArticleMeta[];

        static $inject = ['article', 'articleService'];

        constructor(public article:common.models.Article, private articleService:common.services.article.ArticleService) {

            if(_.isEmpty(article._articleMeta)) {
                this.metas = [];
            }
            else {
                this.metas = article._articleMeta;
            }

        }

        /**
         * Add a new meta tag to article.
         */
        public add():void {

            this.metas.push(
                new common.models.ArticleMeta({
                    metaName:'',
                    metaContent:'',
                    metaProperty:''
                })
            );

        }

        /**
         * Save all metas.
         */
        public save():void {

            this.articleService.saveMetas(this.article, this.metas);

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}