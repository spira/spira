namespace common.services.notification {

    export const namespace = 'common.services.notification';

    export class NotificationService {

        static $inject:string[] = ['$mdToast'];

        constructor(private $mdToast:ng.material.IToastService) {

        }

        public showToast(message:string, parent:string = ''):void {

            let options:any = {
                hideDelay: 2000,
                position: 'top'
            };

            if(_.isEmpty(parent)) { // Show a fixed toast
                options.template = '<md-toast class="md-toast-fixed">' + message + '</md-toast>';
            }
            else { // Show a normal toast on the parent element
                options.template = '<md-toast>' + message + '</md-toast>';
                options.parent = parent;
            }

            this.$mdToast.show(options);
        }

    }

    angular.module(namespace, [])
        .service('notificationService', NotificationService);

}



