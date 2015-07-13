//Import definitely typed definitions

///<reference path="../typings/tsd.d.ts" />

declare module global {

    export interface IState extends ng.ui.IState{
        data: {
            title?: string;
            role: string;
            icon?: string;
            sortAfter?: string;
            navigation?: boolean;
        }
    }

    export interface IStateDefinition {
        name: string;
        options: IState;
    }



}