namespace common.models {

    @common.decorators.changeAware
    export class Article extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _tags: Tag,
            _author: User,
            _articleMetas: this.hydrateMetaCollectionFromTemplate,
            _comments: ArticleComment,
            _contentPieces: contentPieces.ContentPiece,
        };

        public articleId:string = undefined;
        public title:string = undefined;
        public permalink:string = undefined;
        public content:string = undefined;
        public primaryImage:string = undefined;
        public status:string = undefined;
        public authorId:string = undefined;

        public authorDisplay:boolean = undefined;
        public showAuthorPromo:boolean = undefined;

        public _contentPieces:contentPieces.ContentPiece[] = [];
        public _articleMetas:ArticleMeta[] = [];
        public _author:User = undefined;
        public _tags:Tag[] = [];
        public _comments:ArticleComment[] = [];

        private static articleMetaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

        /**
         * Get the article identifier
         * @returns {string}
         */
        public getIdentifier():string {

            return this.permalink || this.articleId;

        }

        /**
         * Hydrates a meta template with meta which already exists
         * @param data
         * @param exists
         * @returns {any}
         */
        private hydrateMetaCollectionFromTemplate(data:any, exists:boolean) {

            return (<any>_).chain(common.models.Article.articleMetaTemplate)
                .map((metaTagName) => {

                    let existingTag = _.find((<common.models.Article>data)._articleMetas, {metaName:metaTagName});
                    if(_.isEmpty(existingTag)) {
                        return new common.models.ArticleMeta({
                            metaName:metaTagName,
                            metaContent:'',
                            articleId:(<common.models.Article>data).articleId,
                            metaId:common.models.Article.generateUUID()
                        });
                    }
                    return existingTag;
                })
                .thru((templateMeta) => {
                    let leftovers = _.filter((<common.models.Article>data)._articleMetas, (metaTag) => {
                        return !_.contains(templateMeta, metaTag);
                    });

                    return templateMeta.concat(leftovers);
                })
                .value();

        }

    }

}



