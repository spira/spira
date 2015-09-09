namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class AuthorContactChip {
        public id:string = undefined;
        public name:string = undefined;
        public image:string = undefined;
        public email:string = undefined;
    }

    export class MetaController {

        static $inject = ['article', 'articleService', 'notificationService', 'userService'];

        public author:AuthorContactChip = undefined;

        constructor(
            public article:common.models.Article,
            private articleService:common.services.article.ArticleService,
            private notificationService:common.services.notification.NotificationService
        ) {
        }

        /**
         * Add a new meta tag to article.
         */
        public add():void {

            this.article._articleMeta.push(
                new common.models.ArticleMeta({
                    newTag:true, // We need to know if a new tag is been added so we can let the user edit the metaName field
                    metaName:'',
                    metaContent:'',
                    metaProperty:''
                })
            );

        }

        /**
         * Remove an existing meta tag
         * @param metaTagName
         */
        public remove(metaTagName:string):void {

            this.articleService.removeMetaTag(this.article, metaTagName)
                .then(() => {
                    this.notificationService.toast('Meta tag has been removed successfully').pop();
                    _.remove(this.article._articleMeta, (metaTag) => {
                        return metaTag.metaName == metaTagName;
                    });
                })
                .catch((error) => {
                    this.notificationService.toast(error.data.message).pop();
                });

        }

        public authorSearch(query:string):void {
        }
    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}