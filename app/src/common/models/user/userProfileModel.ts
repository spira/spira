module common.models {
    export interface IGenderOption {
        label:string;
        value:string;
    }

    export class UserProfile extends AbstractModel {

        public dob:string = undefined;
        public mobile:string = undefined;
        public phone:string = undefined;
        public gender:string = undefined;
        public about:string = undefined;
        public facebook:string = undefined;
        public twitter:string = undefined;
        public pinterest:string = undefined;
        public instagram:string = undefined;
        public website:string = undefined;

        public static genderOptions:IGenderOption[] = [
            {label: 'Male', value: 'M'},
            {label: 'Female', value: 'F'},
            {label: 'Prefer not to say', value: 'N/A'}
        ];

        constructor(data:any) {
            super(data);
        }

    }

}
