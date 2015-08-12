namespace common.services.notification {

    export const namespace = 'common.services.notification';

    export class Toast {

        private toastOptions:any = {};

        constructor(private message:string, private $mdToast:ng.material.IToastService, private $rootScope:global.IRootScope) {
            this.toastOptions = {
                hideDelay: 2000,
                position: 'top',
                template: '<md-toast class="md-toast-fixed">' + message + '</md-toast>',
            };
        }


        /**
         * Override or add toast options
         *
         * @param toastOptions
         * @returns {common.services.notification.Toast}
         */
        public options(toastOptions:any) {

            _.merge(this.toastOptions, toastOptions);
            if(_.has(toastOptions, 'parent')) {
                this.toastOptions.template = '<md-toast>' + this.message + '</md-toast>';
            }

            return this;

        }

        /**
         * Show the toast
         */
        public pop():void {

            this.$mdToast.show(this.toastOptions);

        }

    }

    export class NotificationService {

        static $inject:string[] = ['$mdToast', '$rootScope'];

        constructor(private $mdToast:ng.material.IToastService, private $rootScope:global.IRootScope) {

        }

        /**
         * Get an instance of Toast
         *
         * @param message
         * @return {common.services.notification.Toast}
         */
        public toast(message:string):Toast {
            return new Toast(message, this.$mdToast, this.$rootScope);
        }

    }

    angular.module(namespace, [])
        .service('notificationService', NotificationService);

}



