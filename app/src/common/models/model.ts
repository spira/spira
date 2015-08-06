module common.models {

    export interface IModel{} //@todo add common methods/properties of a Model

    export interface IModelFactory{
        (data:any):IModel;
    }

}



