//Import definitely typed definitions

///<reference path="../typings/tsd.d.ts" />

declare module global {

    export interface IState extends ng.ui.IState {
        data: {
            title?: string;
            role?: string;
            icon?: string;
            sortAfter?: string;
            navigation?: boolean;
        }
    }

    export interface IStateDefinition {
        name: string;
        options: IState;
    }

    export interface IWindowService extends ng.IWindowService {

        Toposort:any;
    }

    export interface IUserCredential{
        userCredentialId: string;
        password: string;
    }

    export interface IUser extends NgJwtAuth.IUser {
        userId:string;
        firstName:string; //make compulsory
        lastName:string; //make compulsory
        _userCredential? : IUserCredential;
    }

    export interface IRootScope extends ng.IRootScopeService {
        socialLogin(type:string, redirectState?:string, redirectStateParams?:Object);
    }


}