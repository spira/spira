namespace common.directives.localizableInput.dialog {

    export const namespace = 'common.directives.localizableInput.dialog';

    export interface ILocalizationMap {
        [regionCode:string] : string;
    }

    export class LocalizableInputDialogController {

        static $inject = ['localizations', 'attributeKey', 'inputNodeName', 'originalValue', 'regionService', '$mdDialog', 'notificationService', 'ngRestAdapter'];

        public selectedIndex:number = 0;
        public localizationMap:ILocalizationMap;

        constructor(public localizations:common.models.Localization<any>[],
                    public attributeKey:string,
                    public inputNodeName:string,
                    public originalValue:string,
                    public regionService:common.services.region.RegionService,
                    private $mdDialog:ng.material.IDialogService,
                    private notificationService:common.services.notification.NotificationService,
                    private ngRestAdapter:NgRestAdapter.NgRestAdapterService
        ) {

            this.localizationMap = _.reduce(regionService.supportedRegions, (localizationMap, region:global.ISupportedRegion) => {
                localizationMap[region.code] = this.getLocalizationValueForRegion(region.code);
                return localizationMap;
            }, {});

        }


        public copyFromOriginal(regionCode:string):void{

            let prevValue = this.localizationMap[regionCode];
            this.localizationMap[regionCode] = this.originalValue;


            let actionName = 'Undo';
            this.notificationService
                .toast('Content Copied')
                .options({parent:'#localizableInputDialog'})
                .action(actionName)
                .pop()
                .then((action:any) => {
                    if(actionName == action){
                        this.localizationMap[regionCode] = prevValue;

                        this.notificationService
                            .toast('Copy Undone')
                            .options({parent:'#localizableInputDialog'})
                            .pop();
                    }
                });
        }

        private getLocalizationValueForRegion(regionCode:string):string {
            let localization =  _.find(this.localizations, {regionCode: regionCode});

            if (!localization){
                return undefined;
            }

            return _.get<string>(localization.localizations, this.attributeKey);
        }

        public saveLocalizations(){

            let updatedLocalizations = _.reduce(this.localizationMap, (updatedLocalizations:common.models.Localization<any>[], translation:string, regionCode:string) => {

                let existing = _.find(this.localizations, {regionCode: regionCode});

                if(existing){
                    if(!translation){
                        (<any>_).set(existing.localizations, this.attributeKey, undefined);
                    }else{
                        (<any>_).set(existing.localizations, this.attributeKey, translation);
                    }
                    updatedLocalizations.push(existing);
                    return updatedLocalizations;
                }

                if(!translation){
                    return updatedLocalizations;
                }

                let newLocalization = new common.models.Localization<any>({
                    localizableId: this.ngRestAdapter.uuid(),
                    localizableType: null, //this is determined by the api
                    localizations: {},
                    regionCode: regionCode,
                });

                (<any>_).set(newLocalization.localizations, this.attributeKey, translation);

                updatedLocalizations.push(newLocalization);

                return updatedLocalizations;

            }, []);

            this.$mdDialog.hide(updatedLocalizations);
        }

        /**
         * allow the user to manually close the dialog
         */
        public cancelDialog() {
            this.$mdDialog.cancel('closed');
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', LocalizableInputDialogController);

}
