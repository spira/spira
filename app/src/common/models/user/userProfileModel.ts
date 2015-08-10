module common.models {
    export interface IGenderOption {
        label:string;
        value:string;
    }

    export class UserProfile implements IModel {

        public dob:string;
        public mobile:string;
        public phone:string;
        public gender:string;
        public about:string;
        public facebook:string;
        public twitter:string;
        public pinterest:string;
        public instagram:string;
        public website:string;

        public static genderOptions:IGenderOption[] = [
            {label: 'Male', value: 'M'},
            {label: 'Female', value: 'F'},
            {label: 'Prefer not to say', value: 'N/A'}
        ];

        constructor(data:any) {
            _.assign(this, data);
        }

    }

}
