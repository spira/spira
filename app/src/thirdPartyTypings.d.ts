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

declare var lil:  lil.lilStatic;