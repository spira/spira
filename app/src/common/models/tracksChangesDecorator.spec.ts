let seededChance = new Chance(1);

let data:any = {
    uuid:seededChance.guid(),
    string:seededChance.string(),
};

@common.models.tracksChanges
class TestModel extends common.models.Model {
    public string;
    public uuid;

    constructor(data){
        super(data);
        _.assign(this, data);
    }
}

(() => {


    describe('TracksChanges decorator', () => {


        it('should instantiate a new model', () => {

            let model = new TestModel(data);

            expect(model).to.be.instanceOf(TestModel);

        });

        it('should have no changes tracked', () => {

            let model = new TestModel(data);

            expect((<common.models.ITracksChangesDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        });

        it('should be able to modify an attribute and see that it has been changed', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            expect((<common.models.ITracksChangesDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.include('string');

        });

        it('should be able to reset the changed property list', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            (<common.models.ITracksChangesDecorator>model).resetChangedProperties();

            expect((<common.models.ITracksChangesDecorator>model).getChangedProperties()).to.be.instanceOf(Array).and.to.be.empty;

        })

    });

})();