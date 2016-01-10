module common.models {
    export interface IGenderOption {
        label:string;
        value:string;
    }

    @common.decorators.changeAware.changeAware
    export class UserProfile extends AbstractModel {

        protected __attributeCastMap:IAttributeCastMap = {
            dob: this.castMomentDate,
        };

        public userId:string;
        public dob:moment.MomentDate;
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

        constructor(data:any, exists:boolean = false) {

            super(data, exists);

            this.hydrate(data, exists);
        }

    }

}
