namespace global {

    export declare class Error {
        public name: string;
        public message: string;
        public stack: string;
        constructor(message?: string);
    }

    export class SpiraException extends Error {

        constructor(public message: string) {
            super(message);
            this.name = 'SpiraException';
            this.message = message;
            this.stack = (<any>new Error()).stack;
        }
        toString() {
            return this.name + ': ' + this.message;
        }
    }

}