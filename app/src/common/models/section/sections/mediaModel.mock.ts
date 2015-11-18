namespace common.models.sections {

    export class MediaMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return Media;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                media: (<any>_).chain(1)
                    .range(seededChance.integer({min:1, max:5}), 1)
                    .map(() => {
                        let media = {
                            type: seededChance.pick(Media.mediaTypes),
                        };

                        switch(media.type){
                            case Media.mediaTypeImage:
                                return _.merge(media, {
                                    _image: common.models.ImageMock.entity(),
                                    caption: seededChance.sentence(),
                                });
                                break;
                            case Media.mediaTypeVideo:

                                let provider = seededChance.pick(Media.videoProviders);
                                return _.merge(media, {
                                    provider: provider.providerKey,
                                    videoId: provider.providerKey == Media.videoProviderVimeo ? chance.string({pool: '0123456789', length:8}) : chance.hash({length: 11}),
                                });
                                break;
                        }

                    })
                    .value(),
                size: _.pick<common.models.sections.ISizeOption, common.models.sections.ISizeOption[]>(common.models.sections.Media.sizeOptions).key,
                alignment: _.pick<common.models.sections.IAlignmentOption, common.models.sections.IAlignmentOption[]>(common.models.sections.Media.alignmentOptions).key,
            };
        }

        public static entity(overrides:Object = {}, exists:boolean = true):Media {
            return <Media> new this().buildEntity(overrides, exists);
        }

    }

}