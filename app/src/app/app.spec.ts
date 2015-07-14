///<reference path="../global.d.ts" />

let expect:Chai.ExpectStatic = chai.expect;

describe('Bootstrap', () => {

    describe('isCurrentUrl', () => {

        let AppCtrl, $location, $scope;

        beforeEach(() => {

            module('app');
        });

        beforeEach(()=> {
            inject(($controller, _$location_, $rootScope) => {
                $location = _$location_;
                $scope = $rootScope.$new();
                AppCtrl = $controller('app.controller', {$location: $location, $scope: $scope});
            })
        });

        it('should pass a dummy test', () => {

            expect(AppCtrl).to.be.ok;
        });


    });

});