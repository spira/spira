(() => {

    let seededChance = new Chance();

    describe('Image Model', () => {

        let imageData = {
            imageId: seededChance.guid(),
            version : Math.floor(chance.date().getTime() / 1000),
            folder : seededChance.word(),
            format : seededChance.pick(['gif', 'jpg', 'png']),
            alt : seededChance.sentence(),
            title : chance.weighted([null, seededChance.sentence()], [1, 2]),
        };

        it('should instantiate a new image', () => {

            let image = new common.models.Image(imageData);

            expect(image).to.be.instanceOf(common.models.Image);

        });

    });

})();