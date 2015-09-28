namespace app.admin.articles.listing {

    export const namespace = 'app.admin.articles.listing';

    export interface IArticlesListingStateParams extends ng.ui.IStateParamsService
    {
        page:number;
    }

    export class ArticlesListingConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/listing/{page:int}',
                params: {
                    page: 1
                },
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticlesListingController',
                        templateUrl: 'templates/app/admin/articles/listing/listing.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    articlesPaginator: (articleService:common.services.article.ArticleService) => {
                        return articleService.getPaginator().setCount(12);
                    },
                    tagsPaginator: (tagService:common.services.tag.TagService) => {
                        return tagService.getPaginator().setCount(10);
                    },
                    usersPaginator: (userService:common.services.user.UserService) => {
                        return userService.getPaginator().setCount(10);
                    },
                    initArticles: (articlesPaginator:common.services.pagination.Paginator, $stateParams:IArticlesListingStateParams) => {
                        return articlesPaginator.getPage($stateParams.page);
                    }
                },
                data: {
                    title: "Articles Listing",
                    icon: 'library_books',
                    navigation: true,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    /* istanbul ignore next:@todo - skipping controller testing */
    export class ArticlesListingController {

        static $inject = ['articlesPaginator', 'tagsPaginator', 'usersPaginator', 'initArticles', '$stateParams'];

        public articles:common.models.Article[] = [];
        public pages:number[] = [];
        public currentPageIndex:number;
        public tagsToFilter:common.models.Tag[] = [];
        public usersToFilter:common.models.User[] = [];
        public queryString:string;

        constructor(
            private articlesPaginator:common.services.pagination.Paginator,
            private tagsPaginator:common.services.pagination.Paginator,
            private usersPaginator:common.services.pagination.Paginator,
            articles,
            public $stateParams:IArticlesListingStateParams
        ) {

            this.articles = articles;

            this.pages = articlesPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        /**
         * Function called when article is searched for.
         */
        public search():void {
            this.articlesPaginator.complexQuery({
                _all: this.queryString,
                _tags: _.pluck(this.usersToFilter, 'tagId'),
                authorId: _.pluck(this.usersToFilter, 'userId')
            })
                .then((articles) => {
                    this.articles = articles;
                })
                .finally(() => { //@todo handle case where search returns no results
                    this.pages = this.articlesPaginator.getPages();
                });
        }

        /**
         * Function used in auto-complete to search for tags.
         * @param query
         * @returns {ng.IPromise<any[]>}
         */
        public searchTags(query:string):ng.IPromise<any> {
            return this.tagsPaginator.query(query);
        }

        /**
         * Function called when a tag is added to the tag filter.
         * @param tag
         */
        public addTagToFilter(tag:common.models.Tag):void {
            this.tagsToFilter.push(tag);
        }

        /**
         * Function used in auto-complete to search for users.
         * @param query
         * @returns {ng.IPromise<any[]>}
         */
        public searchUsers(query:string):ng.IPromise<any> {
            return this.usersPaginator.query(query);
        }

        /**
         * Function called when a user is added to the author filter.
         * @param user
         */
        public addUserToFilter(user:common.models.User):void {
            this.usersToFilter.push(user);
        }


    }

    angular.module(namespace, [])
        .config(ArticlesListingConfig)
        .controller(namespace+'.controller', ArticlesListingController);

}