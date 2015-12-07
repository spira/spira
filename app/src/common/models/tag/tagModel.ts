namespace common.models {

    export interface LinkTagPivot {
        tagGroupId: string;
        tagGroupParentId: string;
    }

    export interface CategoryTagPivot {
        parentTagId?: string;
        tagId?: string;
        required: boolean;
        linkedTagsMustExist: boolean;
        linkedTagsMustBeChildren: boolean;
        linkedTagsLimit: number;
        readOnly: boolean;
    }

    @common.decorators.changeAware
    export class Tag extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _childTags: CategoryTag,
        };

        public tagId:string = undefined;
        public tag:string = undefined;
        public searchable:boolean = undefined;

        public _childTags:CategoryTag[] = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

    export abstract class PivotableTag<PivotType> extends Tag {

        public _pivot:PivotType = undefined;

    }

    export class CategoryTag extends PivotableTag<CategoryTagPivot> {

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

    export class LinkingTag extends PivotableTag<LinkTagPivot> {

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



