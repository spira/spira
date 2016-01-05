namespace common.decorators.angularMaterial {

    // Override functions in md-datepicker and md-calendar directive to get it to work with Moment
    angular.module(app.namespace).decorator(
        "mdDatepickerDirective",
        ['$delegate', ($delegate) => {

            $delegate[0].controller.prototype.configureNgModel = function(ngModelCtrl) {
                this.ngModelCtrl = ngModelCtrl;

                var self = this;
                ngModelCtrl.$render = function() {
                    var value = self.ngModelCtrl.$viewValue;

                    // Remove type checking of ng-model:

                    //if (value && !(value instanceof Date)) {
                    //    throw Error('The ng-model for md-datepicker must be a Date instance. ' +
                    //        'Currently the model is a: ' + (typeof value));
                    //}

                    self.date = value;
                    self.inputElement.value = self.dateLocale.formatDate(value);
                    self.resizeInputElement();
                    self.updateErrorState();
                };
            };

            return $delegate;
        }]
    );

    angular.module(app.namespace).decorator(
        "mdCalendarDirective",
        ['$delegate', ($delegate) => {

            $delegate[0].controller.prototype.setNgModelValue = function(date) {
                // Convert date to a moment object and use that instead
                let momentDate = moment(date);

                this.$scope.$emit('md-calendar-change', momentDate);
                this.ngModelCtrl.$setViewValue(momentDate);
                this.ngModelCtrl.$render();
            };

            return $delegate;
        }]
    );
}