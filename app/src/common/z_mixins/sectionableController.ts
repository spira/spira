namespace common.mixins {

    export abstract class SectionableController extends app.admin.AbstractContentController<SectionableModel, SectionableApiService> {

        /**
         * Update the article sort order
         * @param event
         * @param section
         */
        public sectionUpdated(event:string, section:common.models.Section<any>):void {

            this.entity.updateSectionsDisplay();

            if (event == 'deleted' && section.exists()){
                this.modelService.addQueuedSaveProcessFunction(() => {
                    return this.modelService.deleteSection(this.entity, section);
                });
            }
        }

    }

}