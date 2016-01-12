namespace common.models {

    export interface ICIInfo {
        id: string;
        url: string;
        date: moment.Moment;
    }
    
    export interface ICommitInfo {
        commit: string;
        author: string;
        date: moment.Moment;
        message: string;
        refs: string;
    }

    export class SystemInformation extends AbstractModel {

        protected __attributeCastMap:IAttributeCastMap = {
            appBuildDate: this.castMoment,
            'latestCommit.date': this.castMoment,
            'tagCommit.date': this.castMoment,
        };

        public latestCommit: ICommitInfo;
        public tagCommit: ICommitInfo;
        public appBuildDate:moment.Moment;
        public ciBuild:ICIInfo;
        public ciDeployment:ICIInfo;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



