namespace common.models {

    export interface ITracksChangesDecorator{
        getChangedProperties?():string[];
        resetChangedProperties?():void;
    }

    export function tracksChanges(target: any) {

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

            obj.resetChangedProperties = function(){
                changedProperties = [];
            };

            obj.getChangedProperties = function(){
                return changedProperties;
            };

            _.forIn(obj, (val, propName) => {

                let value = val; //store the value locally

                Object.defineProperty(obj, propName, {
                    enumerable: true,
                    get: function() {
                        return value;
                    },
                    set: function (v) {

                        value = v;

                        if (changedProperties.indexOf(propName) === -1){
                            changedProperties.push(propName);
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