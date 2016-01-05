namespace common.models {

    @common.decorators.changeAware.changeAware
    export class Localization<T extends AbstractModel> extends AbstractModel {

        public localizableId: string;
        public localizableType: string;
        public localizations: T;
        public excerpt: string;
        public title: string;
        public regionCode: string;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



