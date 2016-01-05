namespace config.vendorModules {

    export const namespace = 'config.vendorModules';

    class CloudinaryConfig {


        static $inject = ['cloudinaryProvider'];

        constructor(cloudinaryProvider:any) {

            cloudinaryProvider.config({
                upload_endpoint: 'https://api.cloudinary.com/v1_1/', // default
                cloud_name: 'spira', // required
            });
        }
    }

    class RestAdapterConfig {

        static $inject = ['ngRestAdapterProvider'];

        constructor(ngRestAdapterProvider:NgRestAdapter.NgRestAdapterServiceProvider) {

            ngRestAdapterProvider.configure({
                baseUrl: global.Environment.getApiUrl()
            });

        }
    }

    class AuthConfig {

        static $inject = ['ngJwtAuthServiceProvider'];

        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider) {

            let config:NgJwtAuth.INgJwtAuthServiceConfig = {
                refreshBeforeSeconds: 60 * 10, //10 mins
                checkExpiryEverySeconds: 60, //1 min
                storageKeyName: 'jwtAuthToken',
                apiEndpoints: {
                    base: global.Environment.getApiUrl() + '/auth/jwt',
                    login: '/login',
                    tokenExchange: '/token',
                    refresh: '/refresh',
                },
                cookie: {
                    enabled: true,
                    name: 'jwtAuthToken',
                    topLevelDomain: true,
                }
            };

            if (global.Environment.isLocalhost()){
                config.cookie.topLevelDomain = false;
            }

            ngJwtAuthServiceProvider.configure(config);

        }

    }

    class MarkedInit {

        static $inject = ['marked', '$state'];

        constructor(private marked, private $state:ng.ui.IStateService) {

            let newRenderer = {
                link: (href, title, text) => {

                    let $state:ng.ui.IStateService;

                    let shortcodeMatcher = /(article):(.*)/;
                    let matches = href.match(shortcodeMatcher);
                    if (matches){
                        let state = '.';
                        switch(matches[1]){
                            case 'article':
                                state = 'app.guest.articles.article';
                            break;
                        }

                        href = this.$state.href(state, {permalink:matches[2]});
                    }

                    return `<a href="${href}" target='_blank'>${text}</a>`;
                }
            };

            /**
             * Note the following implementation is a hack to allow renderer to be configured after bootstrap.
             * Ideally this would be implemented with `markedProvider.setRenderer`, however that only work in
             * config phase due to it being a provider. If https://github.com/Hypercubed/angular-marked/issues/37
             * is resolved, edit this feature
             */
            this.marked.defaults.renderer = _.merge(this.marked.defaults.renderer, newRenderer);

        }

    }

    angular.module(namespace, [
        'ngMessages', //nice validation messages
        'ngMaterial', //angular material
        'ui.router', // Handles state changes and routing of the site
        'ui.route', // Module to check for active urls, nothing to do with ui.router
        'ui.keypress', // Module to check for active urls, nothing to do with ui.router
        'ui.inflector', //Module to Humanise strings (camelCased or pipe-case etc)
        'ui.validate', //Module to add custom validation to inputs
        'ngAnimate', //angular animate
        'ngSanitize', //angular sanitise
        'hljs', //syntax highlighted code blocks - https://github.com/pc035860/angular-highlightjs
        'ngHttpProgress', //http request progress meter - https://github.com/spira/angular-http-progress
        'ngRestAdapter', // api helper methods - https://github.com/spira/angular-rest-adapter
        'ngJwtAuth', // json web token authentication - https://github.com/spira/angular-jwt-auth
        'infinite-scroll', //infinite scrolling - https://github.com/sroze/ngInfiniteScroll
        'ui.validate', // Field validator - https://github.com/angular-ui/ui-validate
        'ngFileUpload', // File uploader - https://github.com/danialfarid/ng-file-upload
        //'cloudinary', //directives for displaying cloudinary images (official) - https://github.com/cloudinary/cloudinary_angular
        'angular-cloudinary', //https://github.com/thenikso/angular-cloudinary
        'hc.marked', //markdown parser - https://github.com/Hypercubed/angular-marked
        'angular-carousel', //content carousel - https://github.com/revolunet/angular-carousel
        'md.data.table', //https://github.com/daniel-nagy/md-data-table
    ])
    .config(AuthConfig)
    .config(CloudinaryConfig)
    .config(RestAdapterConfig)
    .run(MarkedInit)

}
