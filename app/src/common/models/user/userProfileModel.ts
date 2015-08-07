module common.models {

    export class UserProfile implements IModel {

        public dob:string;
        public mobile:string;
        public phone:string;

        constructor(data:any) {
            _.assign(this, data);
        }

    }

}
