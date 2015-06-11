//// features/support/world.js
'use strict';

var fs = require('fs');
var webdriver = require('selenium-webdriver');

var buildPhantomDriver = function() {
    return new webdriver.Builder().
        withCapabilities(webdriver.Capabilities.phantomjs()).
        build();
};

var driver = buildPhantomDriver();

var getDriver = function() {
    return driver;
};

var World = function World(callback) {

    var defaultTimeout = 20000;

    this.webdriver = webdriver;
    this.driver = driver;

    this.baseUrl = 'http://local.app.spira.io';
    if (process.env.TRAVIS){
        this.baseUrl = 'http://127.0.0.1:8001';
    }

    if (!!process.env.NGINX_PORT_80_TCP){ //if we are executing from a docker container
        this.baseUrl = 'http://' + process.env.NGINX_PORT_80_TCP_ADDR; //rewrite it to the nginx container tcp address
    }

    this.waitFor = function(cssLocator, timeout) {
        var waitTimeout = timeout || defaultTimeout;
        return driver.wait(function() {
            return driver.isElementPresent({ css: cssLocator });
        }, waitTimeout);
    };

    callback();
};

module.exports.World = World;
module.exports.getDriver = getDriver;