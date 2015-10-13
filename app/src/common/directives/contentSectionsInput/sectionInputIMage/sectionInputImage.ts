namespace common.directives.contentSectionsInput.sectionInputImage {

    export const namespace = 'common.directives.contentSectionsInput.sectionInputImage';

    class SectionInputImageController {

        public selectedIndex:number = 0;
        public section:common.models.Section<common.models.sections.Image>;
        public imageForm:ng.IFormController;
        public alignmentOptions:common.models.sections.IAlignmentOption[];
        public sizeOptions:common.models.sections.ISizeOption[];

        static $inject = ['$mdDialog'];
        constructor(private $mdDialog){

            this.alignmentOptions = common.models.sections.Image.alignmentOptions;
            this.sizeOptions = common.models.sections.Image.sizeOptions;
        }


        public addImage(){

            this.section.content.images.push({
                _image:null,
                caption:null,
                size:null,
                alignment:null,
            });
        }

        public removeImage(image) {
            console.log('removing image');

            var confirm = this.$mdDialog.confirm()
                .title("Are you sure you want to delete this image?")
                .content('This action <strong>cannot</strong> be undone')
                .ariaLabel("Confirm delete")
                .ok("Delete this image!")
                .cancel("Nope! Don't delete it.");

            this.$mdDialog.show(confirm).then(() => {

                this.section.content.images = _.without(this.section.content.images, image);
                this.selectedIndex = this.section.content.images.length - 1;
            });

        }

    }

    class SectionInputImageDirective implements ng.IDirective {

        public restrict = 'E';
        public templateUrl = 'templates/common/directives/contentSectionsInput/sectionInputImage/sectionInputImage.tpl.html';
        public replace = true;
        public scope = {
            section: '=',
        };

        public controllerAs = 'SectionInputImageController';
        public controller = SectionInputImageController;
        public bindToController = true;

        static factory(): ng.IDirectiveFactory {
            return () => new SectionInputImageDirective();
        }
    }

    angular.module(namespace, [])
        .directive('sectionInputImage', SectionInputImageDirective.factory())
    ;


}