//note this file MUST be loaded before any depending classes @todo resolve model load order
namespace common.models {

    export interface IModel{
        getAttributes(includeUnderscoredKeys?:boolean):Object;
        setExists(exists:boolean):void;
        exists():boolean;
    }

    export interface IModelClass{
        new(data?:any, exists?:boolean):IModel;
    }

    export interface IModelFactory{
        (data:any, exists?:boolean):IModel;
    }

    export abstract class AbstractModel implements IModel {

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

            _.forIn(this._nestedEntityMap, (model:IModelClass, nestedKey:string) => {

                //if the nested map is not defined with a leading _ prepend one
                if (!_.startsWith(nestedKey, '_')){
                    nestedKey = '_' + nestedKey;
                }

                if (_.has(data, nestedKey) && !_.isNull(data[nestedKey])){

                    if (_.isArray(data[nestedKey])){
                        this[nestedKey] = _.map(data[nestedKey], (entityData) => this.hydrateModel(entityData, model, exists));
                    }else if (_.isObject(data[nestedKey])){
                        this[nestedKey] = this.hydrateModel(data[nestedKey], model, exists);
                    }

                }else{
                    this[nestedKey] = null;
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
        private hydrateModel(data:any, Model:IModelClass, exists:boolean){

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



