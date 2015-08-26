//note this file MUST be loaded before any depending classes @todo resolve model load order
namespace common.models {

    export interface IModel{} //@todo add common methods/properties of a Model

    export interface IModelFactory{
        (data:any):IModel;
    }

    export class AbstractModel implements IModel {

        protected nestedEntityMap;

        constructor(data?:any) {
            if (data){
                _.assign(this, data);
            }
        }

        /**
         * Find all the nested entities and hydrate them into model instances
         * @param data
         */
        protected hydrateNested(data:any){

            _.forIn(this.nestedEntityMap, (model:typeof AbstractModel, nestedKey:string) => {

                let key = '_' + nestedKey;
                if (_.has(data, key)){

                    if (_.isArray(data[key])){
                        this[key] = _.map(data[key], (entityData) => this.hydrateModel(entityData, model));
                    }else{
                        this[key] = this.hydrateModel(data[key], model);
                    }

                }else{
                    this[key] = null;
                }

            });

        }

        /**
         * Get a new instance of a model from data
         * @param data
         * @param Model
         * @returns {undefined}
         */
        private hydrateModel(data:any, Model:typeof AbstractModel){

            return new Model(data);

        }

    }

}



