namespace common.directives.videoEmbed {

    export const namespace = 'common.directives.videoEmbed';

    export interface IVideoEmbedScope extends ng.IScope {
        provider: string;
        videoId: string;
        url: string;
        iframeToggle: boolean;
    }

    class VideoEmbedDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/videoEmbed/videoEmbed.tpl.html';
        public replace = false;
        public scope = {
            provider: '@',
            videoId: '@',
        };

        constructor(private $sce:ng.ISCEService) {
        }

        public link = ($scope: IVideoEmbedScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes) => {

            //@todo detect when scope property changes and force redraw of the iframe

            $scope.$watchCollection(() => [$scope.videoId, $scope.provider], () => {

                this.reloadIframes($scope);

            });

        };

        private reloadIframes($scope: IVideoEmbedScope):void {

            $scope.iframeToggle = false;

            switch($scope.provider){
                case 'youtube':
                    $scope.url = this.$sce.trustAsResourceUrl(`https://www.youtube.com/embed/${$scope.videoId}?modestbranding=1`);
                    break;

                case 'vimeo':
                    $scope.url = this.$sce.trustAsResourceUrl(`https://player.vimeo.com/video/${$scope.videoId}`);
                    break;

            }

            $scope.iframeToggle = true;
        }

        static factory(): ng.IDirectiveFactory {
            const directive =  ($sce) => new VideoEmbedDirective($sce);
            directive.$inject = ['$sce'];
            return directive;
        }
    }

    angular.module(namespace, [
    ])
        .directive('videoEmbed', VideoEmbedDirective.factory())
    ;


}