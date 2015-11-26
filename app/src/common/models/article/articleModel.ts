namespace common.models {

    @common.decorators.changeAware
    export class Article extends AbstractModel implements mixins.SectionableModel, mixins.TaggableModel, mixins.LocalizableModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _sections: this.hydrateSections,
            _metas: this.hydrateMetaCollectionFromTemplate,
            _author: User,
            _tags: Tag,
            _comments: ArticleComment,
            _localizations: Localization,
            _thumbnailImage: Image,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __primaryKey = 'postId';

        public postId:string = undefined;
        public title:string = undefined;
        public shortTitle:string = undefined;
        public permalink:string = undefined;
        public content:string = undefined;
        public status:string = undefined;
        public authorId:string = undefined;
        public thumbnailImageId:string = undefined;

        public authorDisplay:boolean = undefined;
        public showAuthorPromo:boolean = undefined;
        public sectionsDisplay:mixins.ISectionsDisplay = undefined;

        public _sections:Section<any>[] = [];
        public _metas:Meta[] = [];
        public _author:User = undefined;
        public _tags:LinkingTag[] = [];
        public _comments:ArticleComment[] = [];
        public _localizations:Localization<Article>[] = [];

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

            return this.permalink || this.postId;

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

                    let existingTag = _.find((<common.models.Article>data)._metas, {metaName:metaTagName});
                    if(_.isEmpty(existingTag)) {
                        return new common.models.Meta({
                            metaName:metaTagName,
                            metaContent:'',
                            metaableId:(<common.models.Article>data).postId,
                            metaId:common.models.Article.generateUUID()
                        });
                    }
                    return existingTag;
                })
                .thru((templateMeta) => {
                    let leftovers = _.filter((<common.models.Article>data)._metas, (metaTag) => {
                        return !_.contains(templateMeta, metaTag);
                    });

                    return templateMeta.concat(leftovers);
                })
                .value();

        }

    }


}



