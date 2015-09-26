namespace common.models {

    export interface ISectionsDisplay{
        sortOrder: string[];
    }

    @common.decorators.changeAware
    export class Article extends AbstractModel {

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
        public permalink:string = undefined;
        public content:string = undefined;
        public primaryImage:string = undefined;
        public status:string = undefined;
        public authorId:string = undefined;

        public authorDisplay:boolean = undefined;
        public showAuthorPromo:boolean = undefined;
        public sectionsDisplay:ISectionsDisplay = undefined;

        public _sections:sections.Section[] = [];
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


        protected hydrateSections(data:any, exists:boolean) : sections.Section[]{

            if (!_.has(data, '_sections')){
                return;
            }

            let sectionsChain =  _.chain(data._sections)
                .map((entityData:any) => new sections.Section(entityData, exists));

            if (_.has(data, 'sectionsDisplay.sortOrder')){
                let sortOrder:string[] = data.sectionsDisplay.sortOrder;
                sectionsChain = sectionsChain.sortBy((section:sections.Section) => _.indexOf(sortOrder, section.articleSectionId, false));
            }

            return sectionsChain.value();
        }
    }

}



