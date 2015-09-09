namespace common.decorators {

    export interface IChangeAwareDecorator{
        getChangedProperties?():string[];
        resetChangedProperties?():void;
        getOriginal?():typeof common.models.AbstractModel;
        getChanged?(includeUnderscoredKeys?:boolean):{
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

            Object.defineProperty(obj, 'resetChangedProperties', <PropertyDescriptor>{
                enumerable: false,
                value: function(){
                    obj = construct(original, args, original.name);
                }
            });

            Object.defineProperty(obj, 'getChanged', <PropertyDescriptor>{
                enumerable: false,
                value: function (includeUnderscoredKeys:boolean = false) {
                    let changedObj = obj;

                    let originalObj = construct(original, args, original.name);

                    let result = {};

                    if(!includeUnderscoredKeys) {
                        changedObj = _.omit(changedObj, (value, key) => {
                            return _.startsWith(key, '_');
                        });
                    }

                    _.forEach(changedObj, function(value, key) {
                        if(!_.isEqual(value, originalObj[key])) {
                            result[key] = value;
                        }
                    });

                    return result;
                }
            });

            Object.defineProperty(obj, 'getOriginal', <PropertyDescriptor>{
                enumerable: false,
                value: function(){
                    return construct(original, args, original.name);
                }
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