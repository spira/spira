namespace common.directives.localizableInput.dialog {

    export const namespace = 'common.directives.localizableInput.dialog';


    export class LocalizableInputDialogController {

        public selectedIndex:number;
        public selectedImage:common.models.Image;
        public library:common.models.Image[];
        private imagesPaginator:common.services.pagination.Paginator;
        private perPage:number = 12;
        public pages:number[];
        public currentPage:number = 1;
        private currentPageIndex:number;

        static $inject = ['$mdDialog', 'imageService'];

        constructor(private $mdDialog:ng.material.IDialogService,
                    private imageService:common.services.image.ImageService) {

            this.init();
        }

        private init() {
            this.imagesPaginator = this.imageService.getPaginator().setCount(this.perPage);

            this.imagesPaginator.getPage(this.currentPage)
                .then((images:common.models.Image[]) => {
                    this.library = images;
                    this.pages = this.imagesPaginator.getPages();
                });

            this.currentPageIndex = this.currentPage - 1;
        }

        public toggleImageSelection(selectedImage:common.models.Image) {

            if (this.selectedImage == selectedImage){
                this.selectedImage = null;
            }else{
                this.selectedImage = selectedImage;
            }
        }

        public selectImage() {

            if (!this.selectedImage) {
                this.$mdDialog.cancel('closed');
            }

            this.$mdDialog.hide(this.selectedImage);
        }

        public goToPage(page:number):ng.IPromise<common.models.Image[]>  {

            this.currentPage = page;

            return this.imagesPaginator.getPage(this.currentPage)
                .then((images:common.models.Image[]) => {
                    this.library = images;
                    return this.library;
                });
        }

        /**
         * allow the user to manually close the dialog
         */
        public cancelDialog() {
            this.$mdDialog.cancel('closed');
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', LocalizableInputDialogController);

}
