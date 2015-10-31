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
    
    export interface MouseEventMockInit {
        typeArg?: string;
        canBubbleArg?: boolean;
        cancelableArg?: boolean;
        viewArg?: Window;
        detailArg?: number;
        screenXArg?: number;
        screenYArg?: number;
        clientXArg?: number;
        clientYArg?: number;
        ctrlKeyArg?: boolean;
        altKeyArg?: boolean;
        shiftKeyArg?: boolean;
        metaKeyArg?: boolean;
        buttonArg?: number;
        relatedTargetArg?: EventTarget;
    }

    export class MouseEventMock {

        public static getMock(overrides:MouseEventMockInit = {}):MouseEvent {

            let defaults = {
                typeArg: 'click',
                canBubbleArg: true,
                cancelableArg: true,
                viewArg: window,
                detailArg: 1,
                screenXArg: 800,
                screenYArg: 600,
                clientXArg: 290,
                clientYArg: 260,
                ctrlKeyArg: false,
                altKeyArg: false,
                shiftKeyArg: false,
                metaKeyArg: false,
                buttonArg: 0,
                relatedTargetArg: null,
            };


            let evt = document.createEvent("MouseEvent");

            evt.initMouseEvent.apply(evt, _.values(_.merge(defaults, overrides)));

            return evt;

        }

    }

}