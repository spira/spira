namespace common.models {

    export class ImageMock{

        //constructor(data:any, exists:boolean = false) {
        //
        //}

        private static getMockData() {

            let seededChance = new Chance(Math.random());

            return {
                imageId: seededChance.guid(),
                version : Math.floor(chance.date().getTime() / 1000),
                folder : seededChance.word(),
                format : seededChance.pick(['gif', 'jpg', 'png']),
                alt : seededChance.sentence(),
                title : chance.weighted([null, seededChance.sentence()], [1, 2]),
            };

        }

        public static entity(overrides:Object = {}, exists:boolean = true) {

            let model = new common.models.Image(_.merge(ImageMock.getMockData(), overrides));

            model.setExists(exists);
            return model;
        }

        public static collection(count:number = 10){
            return chance.unique(ImageMock.entity, count);
        }

    }

}