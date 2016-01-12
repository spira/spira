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
    }

    export class SystemInformation extends AbstractModel{

        public latestCommit: ICommitInfo;
        public appBuildDate:moment.Moment;
        public ciBuild:ICIInfo;
        public ciDeployment:ICIInfo;

        constructor(data:any, exists:boolean = false) {
            super(data, exists);
            this.hydrate(data, exists);
        }

    }

}



