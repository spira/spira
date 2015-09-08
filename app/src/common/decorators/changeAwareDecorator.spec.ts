namespace common.decorators {

    let seededChance = new Chance(1);

    let data:any = {
        uuid:seededChance.guid(),
        string:seededChance.string(),
        _nestedData:[
            {test:'foo', testTwo:'bar'}
        ]
    };

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

    describe.only('@changeAware decorator', () => {

        it('should instantiate a new model', () => {

            let model = new TestModel(data);

            expect(model).to.be.instanceOf(TestModel);

        });

        it('should have no changes tracked', () => {

            let model = new TestModel(data);

            expect((<IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

        it('should be able to modify an attribute and see that it has been changed', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            expect((<IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.include('string');

        });

        it('should be able to reset the changed property list', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            (<IChangeAwareDecorator>model).resetChangedProperties();

            expect((<IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

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

        it('should not include nested entities in the changed key-value map if they have not been changed', () => {

            let model = new TestModel(data);

            model.string = 'foo'; //make a change

            expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                string: 'foo'
            });

        });

        it('should not mark a property as changed if it returns to it\'s original value', () => {

            let original = new TestModel(data);
            let model = new TestModel(data);

            model.string = 'foo'; //make a change
            model.string = original.string; //change it back

            expect((<IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

        it('should be able to push a nested attribute and see that it has been changed', () => {

            let model = new TestModel(data);

            model._nestedData.push(new NestedData({test:'foo2', testTwo:'bar2'}));

            expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                _nestedData: [
                    {test:'foo', testTwo:'bar'},
                    {test:'foo2', testTwo:'bar2'}
                ]
            });
        });

        it('should be able to edit a nested attribute and see that it has been changed', () => {

            let model = new TestModel(data);

            model._nestedData[0].test = 'foo2';

            expect((<IChangeAwareDecorator>model).getChanged(true)).to.deep.equal({
                _nestedData: [
                    {test:'foo2', testTwo:'bar'}
                ]
            });
        });

    });

}