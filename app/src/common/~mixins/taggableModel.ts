namespace common.mixins {


    export abstract class TaggableModel extends models.AbstractModel {

        public _tags:common.models.LinkingTag[] = [];

    }

}