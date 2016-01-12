namespace common.models {

    let seededChance = new Chance();

    describe('SystemInformation Model', () => {

        let systemInfoData = {
            "latestCommit": {
                "commit": seededChance.hash(),
                "author": "John Doe <john.doe@example.com>",
                "date": seededChance.date().toISOString(),
                "message": seededChance.sentence()
            },
            "refs": {
                "latestTagOrHead": "/heads/feature/" + seededChance.word,
                "commitRef": seededChance.hash(),
            },
            "appBuildDate": seededChance.date().toISOString(),
            "ciBuild": {
                "id": "%ciBuild.id%",
                "url": "%ciBuild.url%",
                "date": seededChance.date().toISOString()
            },
            "ciDeployment": {
                "id": "%ciDeployment.deploymentId%",
                "url": "%ciDeployment.url%",
                "date": seededChance.date().toISOString()
            }
        };

        it('should instantiate a new system information instance', () => {

            let systemInfo = new SystemInformation(systemInfoData);

            expect(systemInfo).to.be.instanceOf(SystemInformation);

        });

    });

}