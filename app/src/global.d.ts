//Import definitely typed definitions

///<reference path="../typings/tsd.d.ts" />
///<reference path="thirdPartyTypings.d.ts" />

declare namespace global {

    export interface IState extends ng.ui.IState {
        data?: {
            loggedIn?: boolean;
            title?: string;
            role?: string;
            icon?: string;
            sortAfter?: string;
            navigation?: boolean;
            navigationGroup?: string;
        },
        children?:IState[];
    }

    export interface IStateDefinition {
        name: string;
        options: IState;
    }

    export interface IWindowService extends ng.IWindowService {

        Toposort:any;
    }

    export interface IUserCredential{
        userId: string;
        userCredentialId: string;
        password: string;
    }

    export interface ISocialLogin {
        userId:string;
        provider:string;
        token:string;
    }

    export interface IUserData extends NgJwtAuth.IUser {
        _self?:string;
        userId:string;
        username:string;
        firstName?:string;
        lastName?:string;
        emailConfirmed?:string;
        avatarImgUrl?:string;
        country?:string;
        regionCode?:string;
        _userCredential? : IUserCredential;
        _socialLogins? : ISocialLogin[];
    }

    export interface IRootScope extends ng.IRootScopeService {
        socialLogin(type:string, redirectState?:string, redirectStateParams?:Object);
    }

    export interface JwtAuthClaims extends NgJwtAuth.IJwtClaims{
        _user: IUserData;
    }

    export interface ISupportedRegion {
        code:string;
        name:string;
        icon?:string;
    }

}