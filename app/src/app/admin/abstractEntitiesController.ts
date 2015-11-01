namespace app.admin {

    export interface ICommonStateParams extends ng.ui.IStateParamsService
    {
        id:string;
        newEntity:boolean;
    }

    export abstract class AbstractEntitiesController<M extends common.models.AbstractModel, S extends common.services.IExtendedApiService> {

        constructor(
            public entity:M,
            public service:S,
            protected $stateParams:ICommonStateParams,
            protected notificationService:common.services.notification.NotificationService
        ) {
        }

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

    }

}