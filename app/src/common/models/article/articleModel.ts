namespace common.models {

    @common.decorators.changeAware
    export class Article extends AbstractModel implements mixins.SectionableModel, mixins.TaggableModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _tags: Tag,
            _author: User,
            _articleMetas: this.hydrateMetaCollectionFromTemplate,
            _comments: ArticleComment,
            _sections: this.hydrateSections,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        public articleId:string = undefined;
        public title:string = undefined;
        public shortTitle:string = undefined;
        public permalink:string = undefined;
        public content:string = undefined;
        public primaryImage:string = undefined;
        public status:string = undefined;
        public authorId:string = undefined;

        public authorDisplay:boolean = undefined;
        public showAuthorPromo:boolean = undefined;
        public sectionsDisplay:mixins.ISectionsDisplay = undefined;

        public _sections:Section<any>[] = [];
        public _articleMetas:ArticleMeta[] = [];
        public _author:User = undefined;
        public _tags:LinkingTag[] = [];
        public _comments:ArticleComment[] = [];

        private static articleMetaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        public static tagGroups:string[] = [
            'Category', 'Topic'
        ];

        //SectionableModel
        public updateSectionsDisplay: () => void;
        public hydrateSections: (data:any, exists:boolean) => common.models.Section<any>[];

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
         * Get the tag groups
         * @returns {string[]}
         */
        public getTagGroups():string[] {

            return Article.tagGroups;

        }

        /**
         * Hydrates a meta template with meta which already exists
         * @param data
         * @param exists
         * @returns void
         */
        private hydrateMetaCollectionFromTemplate(data:any, exists:boolean):void {

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



