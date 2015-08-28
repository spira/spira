//note this file MUST be loaded before any depending classes @todo resolve model load order
namespace common.models {

    export interface IModel{
        getAttributes(includeUnderscoredKeys?:boolean):Object;
    }

    export interface IModelFactory{
        (data:any):IModel;
    }

    export class AbstractModel implements IModel {

        protected _nestedEntityMap;
        private _exists:boolean;

        constructor(data?:any) {
            this.hydrate(data);

            Object.defineProperty(this, "_exists", {
                enumerable: false,
                writable: true,
                value: false,
            });
        }

        /**
         * Assign the properties of the model from the init data
         * @param data
         */
        protected hydrate(data?:any) {
            if (_.isObject(data)) {
                _.assign(this, data);

                if (_.size(this._nestedEntityMap) > 1) {
                    this.hydrateNested(data);
                }
            }

        }

        /**
         * Find all the nested entities and hydrate them into model instances
         * @param data
         */
        protected hydrateNested(data:any){

            _.forIn(this._nestedEntityMap, (model:typeof AbstractModel, nestedKey:string) => {

                let key = '_' + nestedKey;
                if (_.has(data, key) && !_.isNull(data[key])){

                    if (_.isArray(data[key])){
                        this[key] = _.map(data[key], (entityData) => this.hydrateModel(entityData, model));
                    }else if (_.isObject(data[key])){
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



