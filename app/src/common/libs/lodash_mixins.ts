declare module _ {
    interface LoDashStatic {
        compactObject<T>(object:T, removeFunctions?:boolean):T;
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
         * @param removeFunctions
         */
        compactObject : (object:Object, removeFunctions:boolean = false):Object => {
            var clone = _.clone(object);
            _.each(clone, function(value, key) {
                if(!value || removeFunctions && _.isFunction(value)) {
                    delete clone[key];
                }
            });
            return clone;
        }


    });


}