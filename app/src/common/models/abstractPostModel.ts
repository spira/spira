namespace common.models {

    export abstract class Post extends AbstractModel implements mixins.SectionableModel, mixins.TaggableModel, mixins.LocalizableModel, IMetaableModel, IPermalinkableModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _sections: this.hydrateSections,
            _metas: this.hydrateMetaCollectionFromTemplate,
            _author: User,
            _tags: Tag,
            _comments: Comment,
            _localizations: Localization,
            _thumbnailImage: Image,
        };

        protected __attributeCastMap:IAttributeCastMap = {
            createdAt: this.castMoment,
            updatedAt: this.castMoment,
        };

        protected __primaryKey:string = 'postId';

        public postId:string;
        public title:string;
        public shortTitle:string;
        public permalink:string;
        public content:string;
        public status:string;
        public authorId:string;
        public thumbnailImageId:string;

        public authorOverride:string;
        public showAuthorPromo:boolean;
        public authorWebsite:string;

        public publicAccess:boolean;
        public usersCanComment:boolean;

        public sectionsDisplay:mixins.ISectionsDisplay;

        public _sections:Section<any>[] = [];
        public _metas:Meta[] = [];
        public _author:User;
        public _tags:LinkingTag[] = [];
        public _comments:Comment[] = [];
        public _localizations:Localization<Article>[] = [];

        // SectionableModel
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

        protected metaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        /**
         * Hydrates a meta template with meta which already exists.
         * @param data
         * @param exists
         * @returns {Meta[]}
         */
        private hydrateMetaCollectionFromTemplate(data:any, exists:boolean):Meta[] {

            return (<any>_).chain(this.metaTemplate)
                .map((metaTagName) => {

                    let existingTagData = _.find((<Post>data)._metas, {metaName:metaTagName});
                    if(_.isEmpty(existingTagData)) {
                        return new common.models.Meta({
                            metaName:metaTagName,
                            metaContent:'',
                            metaableId:(<Post>data).postId,
                            metaId:Post.generateUUID()
                        });
                    }

                    return new common.models.Meta(existingTagData);
                })
                .thru((templateMeta) => {

                    let leftovers = _.reduce((<Post>data)._metas, (metaTags:common.models.Meta[], metaTagData) => {
                        if(!_.find(templateMeta, {metaName:metaTagData.metaName})) {
                            metaTags.push(new common.models.Meta(metaTagData));
                        }

                        return metaTags;
                    }, []);

                    return templateMeta.concat(leftovers);
                })
                .value();

        }

    }


}



