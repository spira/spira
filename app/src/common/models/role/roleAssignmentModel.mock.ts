namespace common.models {

    export class RoleAssignmentMock extends AbstractMock{

        public getModelClass():IModelClass {
            return common.models.Image;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                userId: seededChance.guid(),
                roleKey: seededChance.pick(RoleAssignment.knownRoles),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):RoleAssignment {
            return <RoleAssignment> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):RoleAssignment[] {
            return <RoleAssignment[]>new this().buildCollection(count);
        }

    }

}