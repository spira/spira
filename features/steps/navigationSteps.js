'use strict';

var expect = require('chai').expect;
var url = require('url') ;
var webdriver = require('selenium-webdriver');
var until = webdriver.until;

module.exports = function() {
    this.World = require('../support/world.js').World;

    var currentUrl = null;

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

        var driver = this.driver;

        driver.get(this.baseUrl+'/')
        .then(function(){
            return driver.getCurrentUrl();
        }).then(function(url){
            currentUrl = url;
        });

        next();
    });

    this.Then(/^I should see a navigation section$/, function (next) {

        var driver = this.driver;

        driver.isElementPresent({ css: '.navigation' })
            .then(function(navigationPresent) {

                expect(navigationPresent).to.be.true;

                return driver.findElements({ css: '.navigation a'});
            }).then(function(links){
                return driver.wait(until.elementIsVisible(links[0]), 5000);
            }).then(function(){
                next();
            });

    });

    this.Then(/^I click on the menu button$/, function (next) {

        var driver = this.driver;

        driver.isElementPresent({ css: 'button#mobile-menu-toggle' })
        .then(function(menuButtonPresent){
            expect(menuButtonPresent).to.be.true;

            //wait until the navigation has disappeared before trying to open the menu
            return driver.findElement({css: '.navigation a'}).then(function(el) {
                return driver.wait(until.elementIsNotVisible(el), 5000);
            });

        }).then(function(){

            return driver.findElement({css: 'button#mobile-menu-toggle'});
        }).then(function(el){
            return el.click();
        }).then(function(){
            next();
        });

    });

    this.When(/^I click on a navigation item$/, function (next) {

        var driver = this.driver;
        driver.findElements({ css: '.navigation a'})
            .then(function(els){

                var el = els[els.length-1]; //get last element

                return driver.wait(until.elementIsVisible(el), 5000).then(function(){
                    return el;
                });

            }).then(function(el){
                return el.click();
            }).then(function(){
                next();
            });
    });


    this.Then(/^I should see that the page has changed$/, function (next) {

        this.driver.getCurrentUrl()
            .then(function(url){
                expect(url).to.not.equal(currentUrl);
            }).then(function(){
                next();
            });

    });

    this.Then(/^I should see the page I am on highlighted in the navigation$/, function (next) {

        var driver = this.driver;

        driver.getCurrentUrl()
            .then(function(retrievedUrl){
                var currentHref = url.parse(retrievedUrl).pathname;

                return driver.findElement({ css: "a[href*='"+currentHref+"']"});
            }).then(function(el){

                return el.getAttribute('class');
            }).then(function(classes){

                expect(classes).to.contain('selected');

                next();
            });

    });


};