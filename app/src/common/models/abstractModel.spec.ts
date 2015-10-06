namespace common.models {


    class TestChildModel extends AbstractModel {}

    class TestModel extends AbstractModel {

        protected __nestedEntityMap:INestedEntityMap = {
            _hasOne: TestChildModel,
            _hasMany: TestChildModel,
            _hydrate: this.hydrateFunction
        };

        protected __attributeCastMap:IAttributeCastMap = {
            bar: sinon.stub().returns('bar')
        };

        public foo:string = undefined;
        public bar:string = undefined;
        public foobar:string = undefined;
        public _hasOne:TestChildModel;
        public _hasMany:TestChildModel[];
        public _hydrate:TestChildModel[];

        private hydrateFunction(data:any, exists:boolean) {
            if(exists) {
                return ['bar'];
            }

            return data._hydrate;
        }

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

    describe('Abstract Base Model', () => {

        it('should instantiate a new model', () => {

            let model = new TestModel({});

            expect(model).to.be.instanceOf(TestModel);

        });

        it('should instantiate nested entities', () => {

            let model = new TestModel({
                _hasOne : {},
            });

            expect(model._hasOne).to.be.instanceOf(TestChildModel);

        });

        it('should instantiate nested collections (class)', () => {

            let model = new TestModel({
                _hasMany : [{}]
            });

            expect(model._hasMany[0]).to.be.instanceOf(TestChildModel);

        });

        it('should instantiate nested collections (function, exists)', () => {

            let model = new TestModel({
                _hydrate : ['foobar']
            }, true);

            expect(model._hydrate).to.deep.equal(['bar']);

        });

        it('should instantiate nested collections (function, non-existant)', () => {

            let model = new TestModel({
                _hydrate : ['foobar']
            }, false);

            expect(model._hydrate).to.deep.equal(['foobar']);

        });

        it('should be able to retrieve a model\'s attributes without nested keys', () => {

            let model = new TestModel({
                foo: 'bar',
                _hydrate : ['foobar']
            }, false);

            expect(model.getAttributes()).to.deep.equal({
                foo: 'bar',
                bar: undefined,
                foobar: undefined
            });

        });

        it('should be able to retrieve a model\'s attributes with nested keys', () => {

            let model = new TestModel({
                foo: 'bar',
                _hydrate : ['foobar']
            }, false);

            expect(model.getAttributes(true)).to.deep.equal({
                foo: 'bar',
                bar: undefined,
                foobar: undefined,
                _hydrate : ['foobar'],
                _hasOne: null,
                _hasMany: null,
            });

        });


        it('should be able to check if a model exists on the remote api', () => {

            let model = new TestModel({});

            expect(model.exists()).to.be.false;

        });


        it('should be able to set if a model exists on the remote api', () => {

            let model = new TestModel({});
            model.setExists(true);

            expect(model.exists()).to.be.true;

        });

        it('should be able to generate a UUID', () => {

            let uuid:string = TestModel.generateUUID();

            expect(uuid.length).to.equal(36);

        });

        it('should run functions in the attribute cast map', () => {

            let model = new TestModel({
                bar:'foobar',
                foobar:'foobar'
            });

            expect(model.bar).to.equal('bar');

            expect(model.foobar).to.equal('foobar');

        });

    });

}