namespace app.admin.articles.article.post {

    export const namespace = 'app.admin.articles.article.post';

    export class PostController {

        public tags:string[];

        static $inject = ['article', 'tagService', '$scope'];
        constructor(public article:common.models.Article, private tagService:common.services.tag.TagService, private $scope:ng.IScope) {


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
        public updateArticleTags():void{

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

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', PostController);

}