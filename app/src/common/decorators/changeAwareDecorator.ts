namespace common.decorators {

    export interface IChangeAwareDecorator{
        resetChanged?():void;
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

            let pristineInstance = _.cloneDeep(obj);

            Object.defineProperty(obj, 'resetChanged', <PropertyDescriptor>{
                enumerable: false,
                value: function(){

                    pristineInstance = _.cloneDeep(this);
                }
            });

            Object.defineProperty(obj, 'getChanged', <PropertyDescriptor>{
                enumerable: false,
                value: function (includeUnderscoredKeys:boolean = false) {

                    return _.transform(this, (changes, value, key) => {

                        if(!includeUnderscoredKeys && _.startsWith(key, '_')) {
                            return;
                        }

                        // Have to use angular's equals function here as a comparator in the case where you are comparing an object which has been put through an ng-repeat and gotten a hash key added to it
                        if (angular.equals(_.cloneDeep(value), pristineInstance[key])){
                            return;
                        }

                        changes[key] = value;

                    }, {});

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