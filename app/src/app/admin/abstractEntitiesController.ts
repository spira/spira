namespace app.admin {

    export interface ICommonStateParams extends ng.ui.IStateParamsService
    {
        id:string;
        newEntity:boolean;
    }

    export abstract class AbstractEntitiesController<M extends common.models.AbstractModel, S extends common.services.IExtendedApiService> {

        public showPreview:boolean = false;

        constructor(
            public entity:M,
            public service:S,
            protected $stateParams:ICommonStateParams,
            protected notificationService:common.services.notification.NotificationService,
            protected $mdDialog:ng.material.IDialogService,
            protected $state:ng.ui.IStateService
        ) {
        }

        protected abstract getListingState():string;

        /**
         * Save the entity
         * @returns {any}
         */
        public save():ng.IPromise<any> {

            return this.service.save(this.entity)
                .then(() => {
                    this.notificationService.toast('Saved').pop();
                });

        }

        /**
         * Remove the entity, if successful navigate to state returned by getListingState()
         * @returns {IPromise<TResult>}
         */
        public remove():ng.IPromise<any> {

            var confirm = this.$mdDialog.confirm()
                .title("Are you sure you want to delete this item?")
                .htmlContent("This action <strong>cannot</strong> be undone.")
                .ariaLabel("Confirm delete")
                .ok("Delete")
                .cancel("Cancel");

            return this.$mdDialog.show(confirm).then(() => {

                this.$mdDialog.hide();

                this.service.removeModel(this.entity)
                    .then(() => {
                        this.notificationService.toast('Deleted').pop();
                        this.$state.go(this.getListingState());
                    });

            });
        }

        public togglePreview(){
            this.showPreview = !this.showPreview;
        }

    }

}