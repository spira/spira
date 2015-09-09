namespace common.decorators {

    let seededChance = new Chance(1);

    @changeAware
    class TestModel extends common.models.AbstractModel {
        public string;
        public uuid;
        public _nestedData:NestedData[];

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }
    }

    @changeAware
    class NestedData extends common.models.AbstractModel {
        public test:string = undefined;
        public testTwo:string = undefined;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }
    }

    let nestedDataSample = new NestedData({test:'foo', testTwo:'bar'}),
        data:any = {
            uuid:seededChance.guid(),
            string:seededChance.string(),
            _nestedData:[
                nestedDataSample
            ]
        };

    describe('@changeAware decorator', () => {

        it('should instantiate a new model', () => {

            let model = new TestModel(data);

            expect(model).to.be.instanceOf(TestModel);

        });

        it('should be able to reset the changed property list', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            (<IChangeAwareDecorator>model).resetChangedProperties();

            expect((<IChangeAwareDecorator>model).getChanged()).to.be.empty;

        });

        it('should be able to retrieve the original unmodified object', () => {

            let original = new TestModel(data);
            let model = new TestModel(data);

            model.string = 'foo'; //make a change

            expect((<IChangeAwareDecorator>model).getOriginal()).to.deep.equal(original);

        });

        it('should be able to retrieve the changed key-value map', () => {

            let model = new TestModel(data);

            model.string = 'foo'; //make a change

            expect((<IChangeAwareDecorator>model).getChanged()).to.deep.equal({
                string: 'foo'
            });

        });

        describe.only('Nested entities', () => {

            it('should be able to edit a nested attribute and see that it has been changed', () => {

                let model = new TestModel(data);

                model._nestedData[0].test = 'foo2'; // @todo: not sure why this changes the original object, in practice it doesn't

                expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                    _nestedData: [
                        {test:'foo2', testTwo:'bar'}
                    ]
                });
            });

            it('should be able to push a nested attribute and see that it has been changed', () => {

                let model = new TestModel(data);

                model._nestedData.push(new NestedData({test:'foo2', testTwo:'bar2'})); // @todo: not sure why this changes the original object, in practice it doesn't

                expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                    _nestedData: [
                        {test:'foo', testTwo:'bar'},
                        {test:'foo2', testTwo:'bar2'}
                    ]
                });
            });

            it('should not include nested entities in the changed key-value map if they have not been changed', () => {

                let model = new TestModel(data);

                model.string = 'foo'; //make a change

                expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                    string: 'foo'
                });

            });

        });

    });

}