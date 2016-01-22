namespace app.admin {

    export interface ICommonStateParams extends ng.ui.IStateParamsService
    {
        id:string;
        newEntity:boolean;
    }

    export abstract class AbstractEntitiesController<M extends common.models.AbstractModel, S extends common.services.IExtendedApiService> {

        public showPreview:boolean = false;

        public entityForm:ng.IFormController;

        private previousState:ng.ui.IState;
        private previousStateParams:ng.ui.IStateParamsService;

        constructor(
            public entity:M,
            public service:S,
            protected $stateParams:ICommonStateParams,
            protected notificationService:common.services.notification.NotificationService,
            protected $mdDialog:ng.material.IDialogService,
            protected $state:ng.ui.IStateService,
            protected $scope:ng.IScope
        ) {
            // If the user has unsaved changes on the form, ask if they would like to stay on the page.

            let stateChangeOverride:boolean = false;

            $scope.$on('$stateChangeStart', (event, toState, toParams, fromState, fromParams) => {
                if(!_.isEmpty(this.entityForm) && !stateChangeOverride && this.entityForm.$dirty) {
                    event.preventDefault();

                    let confirm = this.$mdDialog.confirm()
                        .title("Are you sure you want to navigate away from this page?")
                        .htmlContent("You have unsaved changes on this form.")
                        .ariaLabel("Confirm navigate away")
                        .ok("Leave")
                        .cancel("Stay");

                    this.$mdDialog.show(confirm)
                        .then(() => {
                            stateChangeOverride = true;
                            $state.go(toState.name, toParams);
                        })
                }
            });

            $scope.$on('$stateChangeSuccess', (event, toState, toParams, fromState, fromParams) => {
                // Save which state we came from so we can return to it later
                this.previousState = fromState;
                this.previousStateParams = fromParams;
            });
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

        /**
         * Returns the user to the previous state.
         */
        public cancel():void {

            if(this.previousState.abstract) {
                this.$state.go('app.admin.dashboard');
            }
            else {
                this.$state.go(this.previousState.name, this.previousStateParams);
            }

        }

        public togglePreview(){
            this.showPreview = !this.showPreview;
        }

    }

}
