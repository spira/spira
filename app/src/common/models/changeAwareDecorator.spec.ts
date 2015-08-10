let seededChance = new Chance(1);

let data:any = {
    uuid:seededChance.guid(),
    string:seededChance.string(),
};

@common.models.changeAware
class TestModel extends common.models.Model {
    public string;
    public uuid;

    constructor(data){
        super(data);
        _.assign(this, data);
    }
}

(() => {


    describe('@changeAware decorator', () => {


        it('should instantiate a new model', () => {

            let model = new TestModel(data);

            expect(model).to.be.instanceOf(TestModel);

        });

        it('should have no changes tracked', () => {

            let model = new TestModel(data);

            expect((<common.models.IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

        it('should be able to modify an attribute and see that it has been changed', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            expect((<common.models.IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.include('string');

        });

        it('should be able to reset the changed property list', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            (<common.models.IChangeAwareDecorator>model).resetChangedProperties();

            expect((<common.models.IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

        it('should be able to retrieve the original unmodified object', () => {


            let original = new TestModel(data);
            let model = new TestModel(data);

            model.string = 'foo'; //make a change

            expect((<common.models.IChangeAwareDecorator>model).getOriginal()).to.deep.equal(original);

        });

        it ('should be able to retrieve the changed key-value map', () => {

            let model = new TestModel(data);

            model.string = 'foo'; //make a change

            expect((<common.models.IChangeAwareDecorator>model).getChanged()).to.deep.equal({
                string: 'foo'
            });

        });

        it ('should not mark a property as changed if it returns to it\'s original value', () => {

            let original = new TestModel(data);
            let model = new TestModel(data);

            model.string = 'foo'; //make a change
            model.string = original.string; //change it back

            expect((<common.models.IChangeAwareDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

    });

})();