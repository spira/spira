namespace common.decorators {

    export interface IChangeAwareDecorator{
        getChangedProperties?():string[];
        resetChangedProperties?():void;
        getOriginal?():typeof common.models.AbstractModel;
        getChanged?():{
            [key:string]: any;
        };
    }

    export function changeAware(target: any) {

        // save a reference to the original constructor
        var original = target;

        // ugly! utility function to rename a function
        function renameFunction(name, fn) {

            return new Function('fn',
                "return function " + name + "(){ return fn.apply(this,arguments)}"
            )(fn);

        }

        // a utility function to generate instances of a class
        function construct(constructor, args, name) {
            var c : any = function () {
                return constructor.apply(this, args);
            };

            c = renameFunction(name, c);

            c.prototype = constructor.prototype;

            return new c();
        }

        // the new constructor behaviour
        var f : any = function (...args) {

            let obj = construct(original, args, original.name);

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
                    return construct(original, args, original.name);
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

        f = _.merge(f, _.clone(original)); //merge in static members

        // copy prototype so intanceof operator still works
        f.prototype = original.prototype;

        // return new constructor (will override original)
        return f;
    }

}