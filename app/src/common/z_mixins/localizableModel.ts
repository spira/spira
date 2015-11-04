namespace common.mixins {

    export abstract class LocalizableModel extends models.AbstractModel {

        public _localizations:common.models.Localization<any>[] = [];
    }

}