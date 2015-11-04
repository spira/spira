declare module _ {
    interface LoDashStatic {
        compactObject<T>(object:T,  recursive?:boolean, removeFunctions?:boolean):Object;
    }
}

namespace common.libs {

    /**
     * lodash_mixins.ts
     * Add mixins (additional functions) to lodash. Call them with _.[fn_name](params)
     * See http://lodash.com/docs#mixin for more info
     */

    _.mixin({

        /**
         * Get an object with all falsy values removed
         * @returns {Object}
         * @param object
         * @param recursive
         *  @param removeFunctions
         */
        compactObject : <T>(object:_.Dictionary<T>, recursive:boolean = false, removeFunctions:boolean = false):Object => {

            return _.transform(object, (result:Object, value:any, key:string) => {
                if(!value || removeFunctions && _.isFunction(value)) {
                    return result;
                }
                result[key] = value;

                if(recursive && _.isObject(value)){
                    result[key] = _.compactObject(value, recursive, removeFunctions);
                }

                return result;
            }, {});

        }


    });


}