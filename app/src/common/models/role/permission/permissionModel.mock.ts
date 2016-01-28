namespace common.models.role {

    export class PermissionMock extends AbstractMock{

        public getModelClass():IModelClass {
            return Permission;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                key: seededChance.word(),
                description:seededChance.sentence(),
                isDefault:false,
                type:'permission',
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Permission {
            return <Permission> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):Permission[] {
            return <Permission[]>new this().buildCollection(count);
        }

    }

}