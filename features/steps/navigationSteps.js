'use strict';

var expect = require('chai').expect;

module.exports = function() {
    this.World = require('../support/world.js').World;

    this.Given(/^I am an anonymous user on a "(.*)" browser$/, function (browserType, next) {

        switch (browserType){
            case 'desktop':
                this.driver.manage().window().setSize(1280, 800);
                break;
            case 'mobile':
                this.driver.manage().window().setSize(375, 667);
                break;
        }

        //do nothing
        next();
    });

    this.When(/^I am on the home page$/, function (next) {

        this.driver.get(this.baseUrl+'/');

        next();
    });

    this.Then(/^I should see a navigation section$/, function (next) {

        this.driver.isElementPresent({ css: '.navigation' }).then(function(navigationPresent) {

            expect(navigationPresent).to.be.true;

            next();
        });

    });

    this.Then(/^I should be able to access the navigation from a button action$/, function (next) {

        var driver = this.driver;

        driver.isElementPresent({ css: 'button#mobile-menu-toggle' })
        .then(function(menuButtonPresent){
            expect(menuButtonPresent).to.be.true;
            return driver.findElement({css: 'button#mobile-menu-toggle'});
        }).then(function(el){
            return el.click();
        }).then(function(){
            return driver.isElementPresent({ css: '.navigation'});
        }).then(function(navigationPresent){

            expect(navigationPresent).to.be.true;

            next();
        });

    });

    this.When(/^I click on a navigation item$/, function (next) {
        // Write code here that turns the phrase above into concrete actions
        next.pending();

        //return this.driver.findElement({ css: '.navigation a'})
        //    .then(function(el){
        //        return el.click();
        //    });
    });


    this.Then(/^I should see that the page has changed$/, function (next) {
        // Write code here that turns the phrase above into concrete actions
        next.pending();
    });

    this.Then(/^I should see the page I am on highlighted in the navigation$/, function (next) {
        // Write code here that turns the phrase above into concrete actions
        next.pending();
    });


};