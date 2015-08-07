module common.models {
    export enum gender {
        M,
        F,
    }

    export class UserProfile implements IModel {

        public dob:string;
        public mobile:string;
        public phone:string;
        public gender:gender;
        public about:string;
        public facebook:string;
        public twitter:string;
        public pinterest:string;
        public instagram:string;
        public website:string;

        constructor(data:any) {
            _.assign(this, data);
        }

    }

}
