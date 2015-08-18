namespace app.guest.forum {

    export const namespace = 'app.guest.forum';

    class ForumConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                resolve: /*@ngInject*/{
                    redirect: ($window:ng.IWindowService, $location:ng.ILocationService) => {

                        let protocol = $location.protocol(),
                            host = $location.host().replace(/local(\.app)?/, 'local.forum');

                        $window.location.href = `${protocol}://${host}/sso`;
                    }
                },
                data: {
                    title: "Forum",
                    loggedIn: true,
                    role: 'user',
                    icon: 'extension',
                    navigation: true,
                    sortAfter: app.guest.articles.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module(namespace, [])
        .config(ForumConfig);

}
