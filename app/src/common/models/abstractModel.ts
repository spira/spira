//note this file MUST be loaded before any depending classes @todo resolve model load order
namespace common.models {

    export interface IModel{} //@todo add common methods/properties of a Model

    export interface IModelFactory{
        (data:any):IModel;
    }

    export class AbstractModel implements IModel {

        constructor(data?:any) {
            if (data){
                _.assign(this, data);
            }
        }

    }

}



