namespace common.models {

    export interface IChangeAwareDecorator{
        getChangedProperties?():string[];
        resetChangedProperties?():void;
        getOriginal?():typeof Model;
        getChanged?():{
            [key:string]: any;
        };
    }

    export function changeAware(target: any) {

        // save a reference to the original constructor
        var original = target;

        // a utility function to generate instances of a class
        function construct(constructor, args) {
            var c : any = function () {
                return constructor.apply(this, args);
            };
            c.prototype = constructor.prototype;
            return new c();
        }

        // the new constructor behaviour
        var f : any = function (...args) {

            let obj = construct(original, args);

            let changedProperties = [];

            Object.defineProperty(obj, 'resetChangedProperties', {
                enumerable: false,
                value: function(){
                    changedProperties = [];
                }
            });

            Object.defineProperty(obj, 'getChangedProperties', {
                enumerable: false,
                value: function(){
                    return changedProperties;
                }
            });

            Object.defineProperty(obj, 'getChanged', {
                enumerable: false,
                value: function(){
                    return _.pick(this, changedProperties);
                }
            });

            Object.defineProperty(obj, 'getOriginal', {
                enumerable: false,
                value: function(){
                    return construct(original, args);
                }
            });

            _.forIn(obj, (val, propName) => {

                let value = val; //store the value locally

                Object.defineProperty(obj, propName, {
                    enumerable: !_.isFunction(value),
                    get: function() {
                        return value;
                    },
                    set: function (v) {

                        value = v;

                        if (changedProperties.indexOf(propName) === -1){
                            changedProperties.push(propName);
                        }else if (v === this.getOriginal()[propName]){
                            changedProperties = _.without(changedProperties, propName); //remove the changed properties if it becomes unchanged
                        }
                    }
                });

            });

            return obj;

        };

        // copy prototype so intanceof operator still works
        f.prototype = original.prototype;

        // return new constructor (will override original)
        return f;
    }

}