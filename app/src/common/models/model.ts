namespace common.models {

    export interface IModel{} //@todo add common methods/properties of a Model

    export interface IModelFactory{
        (data:any):IModel;
    }

    export class Model implements IModel {

        constructor(data:any) {
            _.assign(this, data);
        }

    }

}



