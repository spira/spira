module common.services.article {

    export const namespace = 'common.services.article';

    export interface IArticle {
        title:string;
        body:string;
        permalink:string;
        _author?:global.IUser;
    }

    export class ArticleService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {
        }

        /**
         * Get all articles from the API
         * @returns {any}
         */
        public getAllArticles():ng.IPromise<IArticle[]> {

            return this.ngRestAdapter.get('/articles')
                .then((res) => {
                    return <IArticle[]>res.data;
                })
                ;

        }

    }

    angular.module(namespace, [])
        .service('articleService', ArticleService);

}



