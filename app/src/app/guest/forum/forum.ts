namespace app.guest.forum {

    export const namespace = 'app.guest.forum';

    class ForumConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                resolve: /*@ngInject*/{
                    redirect: ($window:ng.IWindowService, $location:ng.ILocationService) => {

                        $window.location.href = `${$location.protocol()}://${$location.host()}/forum/sso`;
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
