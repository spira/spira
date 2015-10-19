namespace common.models {

    export class RoleAssignmentMock extends AbstractMock{

        public getModelClass():IModelClass {
            return common.models.Image;
        }

        public getMockData():Object {

            let seededChance = new Chance(Math.random());

            return {
                userId: seededChance.guid(),
                roleKey: seededChance.pick(RoleAssignment.knownRoles),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Image {
            return <Image> new this().buildEntity(overrides, exists);
        }

        public static collection(count:number = 10):Image[] {
            return <Image[]>new this().buildCollection(count);
        }

    }

}