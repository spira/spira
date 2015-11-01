namespace common.models {

    export interface IMock {
        getModelClass():IModelClass;
        getMockData():Object;
    }

    export interface IMockStatic {
        new():IMock;
        entity(overrides?:Object, exists?:boolean):IModel;
        collection(count?:number, overrides?:Object, exists?:boolean):IModel[]
    }

    export abstract class AbstractMock{

        public abstract getMockData():Object;
        public abstract getModelClass():IModelClass;

        public buildEntity(overrides:Object = {}, exists:boolean = true):IModel {

            let data:any = this.getMockData();
            let modelClass = this.getModelClass();

            return new modelClass(_.merge(data, overrides), exists);
        }

        public buildCollection(count:number = 10, overrides:Object = {}, exists:boolean = true){

            return chance.unique(() => this.buildEntity(overrides, exists), count);
        }

    }

}