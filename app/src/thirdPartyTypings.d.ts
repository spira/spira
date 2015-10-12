/**
 * https://github.com/lil-js/uuid
 */
declare module lil {

    interface lilStatic {

        /**
         * Generate a random UUID.
         * @returns {string} A version 4 UUID string.
         */
        uuid():string;

        /**
         * Check if a given string has a valid UUID format. Supports multiple versions (3, 4 and 5)
         * @returns {boolean}
         */
        isUUID(uuid:string, version?:number):boolean;

    }

}

declare var lil: lil.lilStatic;

/**
 * https://github.com/NextStepWebs/simplemde-markdown-editor
 */
declare module SimpleMDE {

    interface CodeMirror {
        on(event:string, handler: () => any);
    }

    interface MDETool {
        [key:string]: string;
    }

    interface SimpleMDEConfig {
        element: HTMLElement; // {DOM Element} [required]
        toolbar?: boolean|string[]|MDETool[]; //https://github.com/NextStepWebs/simplemde-markdown-editor/#toolbar-icons
        autofocus?: boolean;
        autosave?: {
            enabled?: boolean;
            unique_id?: string;
            delay?: number;
        };
        hideIcons?: string[];
        indentWithTabs?: boolean;
        initialValue?: string;
        lineWrapping?: boolean;
        parsingConfig?: {
            allowAtxHeaderWithoutSpace?: boolean;
            strikethrough?: boolean;
            underscoresBreakWords?: boolean;
        };
        previewRender?: (plainText?:string, preview?:HTMLElement) => string;
        renderingConfig?: {
            singleLineBreaks?: boolean;
            codeSyntaxHighlighting?: boolean;
        };
        spellChecker?: boolean;
        status?: boolean|string[]; // Optional usage
        tabSize?: number;
        toolbarTips?: boolean;
    }

    interface SimpleMDEStatic {

        new(config: SimpleMDEConfig): SimpleMDE;

    }

    interface SimpleMDE {

        value():string;
        value(value:string):SimpleMDE;
        codemirror:CodeMirror;

    }

}

declare var simpleMDE: SimpleMDE.SimpleMDE;
declare var SimpleMDE: SimpleMDE.SimpleMDEStatic;