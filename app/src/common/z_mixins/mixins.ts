namespace common.mixins {

    //see http://www.typescriptlang.org/Handbook#mixins for more info on mixins
    export function applyMixins(derivedClass: any, mixinClasses: any[]) {

        _.each(mixinClasses, (mixinClass) => {

            _.each(Object.getOwnPropertyNames(mixinClass.prototype), (name) => {

                derivedClass.prototype[name] = mixinClass.prototype[name];
            });
        });

    }
}