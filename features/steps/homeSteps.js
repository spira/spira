'use strict';

var expect = require('chai').expect;
var webdriver = require('selenium-webdriver');
var until = webdriver.until;

module.exports = function() {
    this.World = require('../support/world.js').World;

    this.Given(/^I am an anonymous user$/, function (next) {
        //do nothing
        next();
    });

    this.When(/^I go to the home page$/, function (next) {

        this.driver.get(this.baseUrl+'/');

        next();
    });

    this.Then(/^I should see "(.*)" as the page title$/, function (title, next) {

        this.driver.wait(until.titleIs(title), 1000).then(function(){
            next();
        });

    });

    this.Then(/^I should see "(.*)" as the main heading$/, function (heading, next) {

        this.driver.findElement({ css: 'h1' }).getText()
            .then(function(text) {

                expect(text).to.equal(heading);

                next();
            });

    });

};