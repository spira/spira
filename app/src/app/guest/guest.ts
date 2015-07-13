
///<reference path="../../global.d.ts" />

module app.guest {

    export const namespace = 'app.guest';

    angular.module('app.guest', [
        'app.guest.home',
        'app.guest.articles',
        'app.guest.sandbox',
        'app.guest.error'
    ]);

}