//note this file MUST be loaded before any depending classes @todo resolve model load order
namespace common.models {

    export interface IModel{
        getAttributes(includeUnderscoredKeys?:boolean):Object;
    }

    export interface IModelFactory{
        (data:any, exists?:boolean):IModel;
    }

    export class AbstractModel implements IModel {

        protected _nestedEntityMap;
        private _exists:boolean;

        constructor(data?:any, exists:boolean = false) {
            this.hydrate(data, exists);

            Object.defineProperty(this, "_exists", {
                enumerable: false,
                writable: true,
                value: exists,
            });
        }

        /**
         * Assign the properties of the model from the init data
         * @param data
         * @param exists
         */
        protected hydrate(data:any, exists:boolean) {
            if (_.isObject(data)) {
                _.assign(this, data);

                if (_.size(this._nestedEntityMap) > 1) {
                    this.hydrateNested(data, exists);
                }
            }

        }

        /**
         * Find all the nested entities and hydrate them into model instances
         * @param data
         * @param exists
         */
        protected hydrateNested(data:any, exists:boolean){

            _.forIn(this._nestedEntityMap, (model:typeof AbstractModel, nestedKey:string) => {

                let key = '_' + nestedKey;
                if (_.has(data, key) && !_.isNull(data[key])){

                    if (_.isArray(data[key])){
                        this[key] = _.map(data[key], (entityData) => this.hydrateModel(entityData, model, exists));
                    }else if (_.isObject(data[key])){
                        this[key] = this.hydrateModel(data[key], model, exists);
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
         * @returns {common.models.AbstractModel}
         * @param exists
         */
        private hydrateModel(data:any, Model:typeof AbstractModel, exists:boolean){

            let model = new Model(data);
            model.setExists(exists);

            return model;
        }

        /**
         * Get all enumerable attributes of the model, by default excluding all keys starting with an _underscore
         * @param includeUnderscoredKeys
         * @returns {any}
         */
        public getAttributes(includeUnderscoredKeys:boolean = false):Object{

            let attributes = _.clone(this);

            if (includeUnderscoredKeys){
                return attributes;
            }

            return _.omit(attributes, (value, key) => {
                return _.startsWith(key, '_');
            });

        }

        /**
         * Get if the model exists in remote api
         * @returns {boolean}
         */
        public exists():boolean{
            return this._exists;
        }

        /**
         * Set if the model exists
         * @param exists
         */
        public setExists(exists:boolean):void{
            this._exists = exists;
        }

    }

}



