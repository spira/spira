namespace common.mixins {

    export abstract class MetaableApiService extends common.services.AbstractApiService {

        private static metaTemplate:string[] = [
            'name', 'description', 'keyword', 'canonical'
        ];

        public hydrateMetaCollection(entity:models.IMetaableModel):common.models.Meta[] {

            return (<any>_).chain(MetaableApiService.metaTemplate)
                .map((metaTagName) => {

                    let existingTag = _.find(entity._metas, {metaName:metaTagName});
                    if(_.isEmpty(existingTag)) {
                        return new common.models.Meta({
                            metaName: metaTagName,
                            metaContent: '',
                            metaableId: entity.getKey(),
                            metaId: this.ngRestAdapter.uuid()
                        });
                    }

                    return existingTag;
                })
                .thru((templateMeta) => {
                    let leftovers = _.filter(entity._metas, (metaTag) => {
                        return !_.contains(templateMeta, metaTag);
                    });

                    return templateMeta.concat(leftovers);
                })
                .value();

        }

    }

}