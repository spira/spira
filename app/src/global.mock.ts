namespace global {

    export class FormControllerMock{

        public static getMock(overrides:Object = {}):ng.IFormController {

            let defaults = {
                $pristine: false,
                $dirty: true,
                $valid: true,
                $invalid: false,
                $submitted: false,
                $error: {},
                $addControl: sinon.stub(),
                $removeControl: sinon.stub(),
                $setValidity:sinon.stub(),
                $setDirty: sinon.stub(),
                $setPristine: sinon.stub(),
                $commitViewValue: sinon.stub(),
                $rollbackViewValue: sinon.stub(),
                $setSubmitted: sinon.stub(),
                $setUntouched: sinon.stub(),
            };

            return <ng.IFormController>_.merge(defaults, overrides);

        }

    }

}