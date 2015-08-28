namespace common.models {


    class TestChildModel extends AbstractModel {}

    class TestModel extends AbstractModel {

        protected _nestedEntityMap = {
            hasOne: TestChildModel,
            hasMany: TestChildModel
        };

        public _hasOne:TestChildModel;
        public _hasMany:TestChildModel[];

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
                '_hasOne' : {},
            });

            expect(model._hasOne).to.be.instanceOf(TestChildModel);

        });

        it('should instantiate nested collections', () => {

            let model = new TestModel({
                '_hasMany' : [{}]
            });

            expect(model._hasMany[0]).to.be.instanceOf(TestChildModel);

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


    });

}