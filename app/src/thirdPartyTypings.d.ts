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
 * https://github.com/sofish/pen
 */
declare module Pen {

    interface PenConfig {
        editor: HTMLElement; // {DOM Element} [required]
        class?: string; // {String} class of the editor,
        debug?: boolean; // {Boolean} false by default
        textarea?: string; // fallback for old browsers
        list?: string[]; // editor menu list
    }

    interface PenStatic {

        new(config: PenConfig): Pen;

    }

    interface Pen {

        destroy():Pen;
        rebuild():Pen;
        toMd():string;

    }

}

declare var pen: Pen.Pen;
declare var Pen: Pen.PenStatic;