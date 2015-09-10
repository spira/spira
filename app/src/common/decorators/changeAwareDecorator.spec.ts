namespace common.decorators {

    let seededChance = new Chance(1);

    @changeAware
    class TestModel extends common.models.AbstractModel {

        protected _nestedEntityMap = {
            _nestedCollection: NestedData,
            _nestedEntity: NestedData,
        };

        public string;
        public uuid;
        public _nestedCollection:NestedData[];
        public _nestedEntity:NestedData;

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

    let nestedCollection = [{test: 'collection1', testTwo: 'collection1'}],
        nestedEntity = {test: 'entity', testTwo: 'entity'},
        data:any = {
            uuid: seededChance.guid(),
            string: seededChance.string(),
            _nestedEntity: nestedEntity,
            _nestedCollection: nestedCollection,
        };

    describe('@changeAware decorator', () => {

        it('should instantiate a new nested model', () => {

            let model = new TestModel(data);

            expect(model).to.be.instanceOf(TestModel);
            expect(model._nestedEntity).to.be.instanceOf(NestedData);
            expect(model._nestedCollection).to.be.instanceOf(Array);
            expect(model._nestedCollection[0]).to.be.instanceOf(NestedData);

        });

        it('should be able to reset the changed property list', () => {

            let model = new TestModel(data);

            model.string = 'foo';

            (<IChangeAwareDecorator>model).resetChangedProperties();

            expect(model.string).to.equal('foo');
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

            it('should be able to edit a nested collection and see that it has been changed', () => {

                let model = new TestModel(data);

                console.log('model', model);

                model._nestedCollection[0].test = 'foo2'; // @todo: not sure why this changes the original object, in practice it doesn't

                let changed = (<IChangeAwareDecorator>model).getChanged(true);

                expect(changed).to.have.property('_nestedCollection');
                expect((<any>changed)._nestedCollection[0]).to.be.instanceOf(NestedData);

                expect(_.cloneDeep(changed)).to.deep.equal({
                    _nestedCollection: [
                        {
                            test: 'foo2',
                            testTwo: nestedCollection[0].testTwo
                        }
                    ]
                });
            });

            it('should be able to push a nested attribute and see that it has been changed', () => {

                let model = new TestModel(data);

                model._nestedCollection.push(new NestedData({test: 'foo2', testTwo: 'bar2'})); // @todo: not sure why this changes the original object, in practice it doesn't

                let changed = (<IChangeAwareDecorator>model).getChanged(true);

                expect(changed).to.have.property('_nestedCollection');
                expect((<any>changed)._nestedCollection[0]).to.be.instanceOf(NestedData);

                console.log('a', JSON.stringify(changed));

                expect(_.cloneDeep(changed)).to.deep.equal({
                    _nestedCollection: [
                        {test: 'collection1', testTwo: 'collection1'},
                        {test: 'foo2', testTwo: 'bar2'}
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