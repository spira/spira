namespace common.services.notification {

    export const namespace = 'common.services.notification';

    export class Toast {

        private toastOptions:any = {};

        private timeOut:number;

        constructor(
            private message:string,
            private $mdToast:ng.material.IToastService,
            private $rootScope:global.IRootScope,
            private $timeout:ng.ITimeoutService
        ) {
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
         * Add a delay before showing the toast
         *
         * @param milliseconds
         */
        public delay(milliseconds:number) {

            this.timeOut = milliseconds;

            return this;

        }

        /**
         * Show the toast
         */
        public pop():void {

            if(_.isNumber(this.timeOut)) {
                // See: https://docs.angularjs.org/api/ng/service/$timeout. ITimeoutService does not have final param
                // which is passed into your function.
                (<any>this.$timeout)(this.$mdToast.show, this.timeOut, true, this.toastOptions);
            }
            else {
                this.$mdToast.show(this.toastOptions);
            }

        }

    }

    export class NotificationService {

        static $inject:string[] = ['$mdToast', '$rootScope', '$timeout'];

        constructor(
            private $mdToast:ng.material.IToastService,
            private $rootScope:global.IRootScope,
            private $timeout:ng.ITimeoutService
        ) {
        }

        /**
         * Get an instance of Toast
         *
         * @param message
         * @return {common.services.notification.Toast}
         */
        public toast(message:string):Toast {
            return new Toast(message, this.$mdToast, this.$rootScope, this.$timeout);
        }

    }

    angular.module(namespace, [])
        .service('notificationService', NotificationService);

}



