namespace common.models {

    export class TagMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Tag;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                tagId: seededChance.guid(),
                tag: seededChance.word(),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Tag {
            return <Tag> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Tag[] {
            return <Tag[]>new this().buildCollection(count, overrides, exists);
        }

    }

    export class CategoryTagMock extends TagMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.CategoryTag;
        }

        public getMockData():Object {

            let tag:any = super.getMockData();

            tag._pivot = {
                required: true,
                linkedTagsMustExist: false,
                linkedTagsMustBeChildren: false,
                linkedTagsLimit: null,
                readOnly: false
            };

            return tag;

        }

        public static entity(overrides:Object = {}, exists:boolean = true):CategoryTag {
            return <CategoryTag> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):CategoryTag[] {
            return <CategoryTag[]>new this().buildCollection(count, overrides, exists);
        }

    }

    export class LinkingTagMock extends TagMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.LinkingTag;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            let tag:any = super.getMockData();

            tag._pivot =  {
                tagGroupId:seededChance.guid()
            };

            return tag;

        }

        public static entity(overrides:Object = {}, exists:boolean = true):LinkingTag {
            return <LinkingTag> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):LinkingTag[] {
            return <LinkingTag[]>new this().buildCollection(count, overrides, exists);
        }

    }

}