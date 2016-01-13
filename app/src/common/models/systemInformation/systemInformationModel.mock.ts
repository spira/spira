namespace common.models {

    export class SystemInformationMock extends AbstractMock implements IMock {

        public getModelClass():IModelClass {
            return SystemInformation;
        }

        public getMockData():Object {

            let seededChance = new Chance();

            return {
                appBuildDate: seededChance.date().toISOString(),
                latestCommit: {
                    commit: seededChance.hash(),
                    author: "John Doe <john.doe@example.com>",
                    date: seededChance.date().toISOString(),
                    message: seededChance.sentence()
                },
                refs: {
                    commit: seededChance.hash(),
                    author: "John Doe <john.doe@example.com>",
                    date: seededChance.date().toISOString(),
                    message: seededChance.sentence()
                },
                ciBuild: {
                    id: "%ciBuild.id%",
                    url: "%ciBuild.url%",
                    date: seededChance.date().toISOString()
                },
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true):SystemInformation {
            return <SystemInformation>new this().buildEntity(overrides, exists);
        }

    }

}