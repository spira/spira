namespace common.directives.contentSectionsInput.sectionInputMedia {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputMedia';

    export class SectionInputMediaController {

        public selectedIndex:number = 0;
        public section:common.models.Section<common.models.sections.Media>;
        public mediaForm:ng.IFormController;
        public alignmentOptions:common.models.sections.IAlignmentOption[];
        public sizeOptions:common.models.sections.ISizeOption[];
        public videoProviders:common.models.sections.IVideoProvider[];
        public videoProviderMap:{[providerKey:string]:common.models.sections.IVideoProvider};

        static $inject = ['$mdDialog', '$mdBottomSheet'];
        constructor(private $mdDialog:ng.material.IDialogService,
                    private $mdBottomSheet:ng.material.IBottomSheetService
        ){

            this.alignmentOptions = common.models.sections.Media.alignmentOptions;
            this.sizeOptions = common.models.sections.Media.sizeOptions;
            this.videoProviders = common.models.sections.Media.videoProviders;
            this.videoProviderMap = _.indexBy(this.videoProviders, 'providerKey');
        }

        /**
         * Add empty media tab
         * @returns {number}
         */
        public addMedia():number{

            return this.section.content.media.push({
                type: common.models.sections.Media.mediaTypeImage, //default type
                _image: null,
                caption: null
            });

        }

        /**
         * When image content changes update caption to alt by default
         * @param imageContent
         */
        public imageChanged(imageContent:common.models.sections.IImageContent):void {

            if (!imageContent.caption){
                imageContent.caption = imageContent._image.alt;
            }

        }

        public mediaTypeChanged(media:(common.models.sections.IImageContent|common.models.sections.IVideoContent)):void{



        }

        /**
         * Delete an image with prompt
         * @param media
         * @returns {IPromise<number>}
         */
        public removeMedia(media:(common.models.sections.IImageContent|common.models.sections.IVideoContent)):ng.IPromise<number> {

            var confirm = this.$mdDialog.confirm()
                .title(`Are you sure you want to delete this ${media.type}?`)
                .content('This action <strong>cannot</strong> be undone')
                .ariaLabel("Confirm delete")
                .ok(`Delete this ${media.type}!`)
                .cancel("Nope! Don't delete it.");

            return this.$mdDialog.show(confirm).then(() => {

                this.section.content.media = _.without(this.section.content.media, media);
                this.selectedIndex = this.section.content.media.length - 1;

                return this.section.content.media.length;
            });

        }

    }

    class SectionInputMediaDirective implements ng.IDirective {

        public restrict = 'E';
        public require = ['sectionInputMedia', '^contentSectionsInputItem'];
        public templateUrl = 'templates/common/directives/contentSectionsInput/types/sectionInputMedia/sectionInputMedia.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputMediaController';
        public controller = SectionInputMediaController;
        public bindToController = true;

        public link = ($scope: ng.IScope, $element: ng.IAugmentedJQuery, $attrs: ng.IAttributes, $controllers: [SectionInputMediaController, item.ContentSectionsInputItemController]) => {

            let mediaController = $controllers[0];
            let parentSectionController = $controllers[1];

            parentSectionController.registerSettingsBindings({
                controller: mediaController,
                controllerAs: this.controllerAs,
                templateUrl: 'templates/common/directives/contentSectionsInput/types/sectionInputMedia/sectionInputMediaSettingsMenu.tpl.html',
            });

        };

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputMediaDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputMedia', SectionInputMediaDirective.factory())
    ;


}