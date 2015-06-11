'use strict';

var expect = require('chai').expect;

module.exports = function() {
    this.World = require('../support/world.js').World;

    this.Given(/^I am an anonymous user$/, function (next) {
        //do nothing
        next();
    });

    this.When(/^I go to the home page$/, function (next) {
        this.driver.get('http://local.app.spira.io');

        next();

    });

    this.Then(/^I should see "(.*)" as the page title$/, function (title, next) {

        this.driver.getTitle().then(function(pageTitle) {

            expect(pageTitle).to.equal(title);

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