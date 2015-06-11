// features/step_definitions/myStepDefinitions.js

module.exports = function () {
    this.World = require("../support/world.js").World; // overwrite default World constructor

    this.Given(/^I am an anonymous user$/, function (callback) {
        // Write code here that turns the phrase above into concrete actions
        callback();
    });

    this.When(/^I go to the home page$/, function (callback) {

        this.visit('http://local.app.spira.io', callback);

    });

    this.Then(/^I should see "(.*)" as the page title$/, function (title, callback) {
        // matching groups are passed as parameters to the step definition

        var pageTitle = this.browser.text('title');
        if (title === pageTitle) {
            callback();
        } else {
            callback.fail(new Error("Expected to be on home page with title " + title+". Title was "+pageTitle));
        }
    });

    this.Then(/^I should see "(.*)" as the main heading$/, function (heading, callback) {
        // matching groups are passed as parameters to the step definition

        var pageHeading = this.browser.text('h1');
        if (heading === pageHeading) {
            callback();
        } else {
            callback.fail(new Error("Expected to be on page with heading " + heading+". Heading was "+pageHeading));
        }
    });

};