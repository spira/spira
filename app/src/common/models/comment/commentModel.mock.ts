namespace common.models {

    export class CommentMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return common.models.Comment;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                commentId: seededChance.guid(),
                body: seededChance.paragraph(),
                createdAt: moment(seededChance.date())
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):Comment {
            return <Comment> new this().buildEntity(overrides, exists);
        }

        // Commented out as this method is unused and reducing code coverage
        //public static collection(count:number = 10, overrides:Object = {}, exists:boolean = true):Comment[] {
        //    return <Comment[]>new this().buildCollection(count, overrides, exists);
        //}

    }

}