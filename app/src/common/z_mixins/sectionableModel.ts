namespace common.mixins {

    export interface ISectionsDisplay{
        sortOrder: string[];
    }

    export abstract class SectionableModel extends models.AbstractModel {

        public _sections:common.models.Section<any>[] = [];
        public sectionsDisplay:ISectionsDisplay;
        /**
         * Update the sort order display to match the section object
         */
        public updateSectionsDisplay():void {
            if (_.isEmpty(this._sections)){
                return;
            }

            //if the sections display attribute is null, define it
            if (!this.sectionsDisplay){
                this.sectionsDisplay = {
                    sortOrder: [],
                }
            }

            let sectionOrder:string[] = _.map(this._sections, (section:common.models.Section<any>) => {
                return section.sectionId;
            });

            if (!_.isEqual(this.sectionsDisplay.sortOrder, sectionOrder)){ //only update the value if it has changed
                this.sectionsDisplay.sortOrder = sectionOrder;
            }
        }

        /**
         * Hydrate the data:
         * - Pre-sort the sections based on the sectionsDisplay field
         * @param data
         * @param exists
         * @returns {any}
         */
        public hydrateSections(data:any, exists:boolean) : common.models.Section<any>[] {

            if (!_.has(data, '_sections')){
                return;
            }

            let sectionsChain =  _.chain(data._sections)
                .map((entityData:any) => new common.models.Section(entityData, exists));

            if (_.has(data, 'sectionsDisplay.sortOrder')){
                let sortOrder:string[] = data.sectionsDisplay.sortOrder;
                sectionsChain = sectionsChain.sortBy((section:common.models.Section<any>) => _.indexOf(sortOrder, section.sectionId, false));
            }

            return sectionsChain.value();
        }
    }

}