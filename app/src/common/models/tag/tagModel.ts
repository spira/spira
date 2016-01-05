namespace common.models {

    export interface ICategorizedTags {
        (category:string):CategoryTagWithChildren;
    }

    export interface CategoryTagWithChildren extends CategoryTag {
        _tagsInCategory:LinkingTag[];
    }

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

    @common.decorators.changeAware.changeAware
    export class Tag extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _childTags: CategoryTag,
        };

        public tagId:string;
        public tag:string;
        public searchable:boolean;

        public _childTags:CategoryTag[] = [];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

    export abstract class PivotableTag<PivotType> extends Tag {

        public _pivot:PivotType;

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



