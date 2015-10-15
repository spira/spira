namespace app.admin.articles.article.post {

    export const namespace = 'app.admin.articles.article.post';

    export class PostController {

        public tags:string[];

        static $inject = ['article', 'tagService', '$scope', 'articleService'];
        constructor(
            public article:common.models.Article,
            private tagService:common.services.tag.TagService,
            public $scope:ng.IScope,
            private articleService:common.services.article.ArticleService
        ) {


            this.tags = _.pluck(article._tags, 'tag');

            $scope.$watchCollection(() => this.tags, (newValue, oldValue) => {
                if (!_.isEqual(newValue, oldValue)){
                    this.updateArticleTags();
                }
            });

        }

        /**
         * Update the article tags on the article model. If new, create a new Tag model.
         */
        public updateArticleTags():void {

            this.article._tags = _.chain(this.tags)
                .map((tag:string):common.models.Tag => {
                    let tagModel:common.models.Tag;

                    if (tagModel = _.find(this.article._tags, {tag: tag})){
                        return tagModel;
                    }

                    tagModel = this.tagService.newTag();
                    tagModel.tag = tag;

                    return tagModel;

                })
                .value();

        }

        /**
         * Update the article sort order
         * @param event
         * @param section
         */
        public sectionUpdated(event:string, section:common.models.Section<any>):void {

            this.article.updateSectionsDisplay();

            if (event == 'deleted' && section.exists()){
                this.articleService.addQueuedSaveProcessFunction(() => {
                    return this.articleService.deleteSection(this.article, section);
                });
            }
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', PostController);

}