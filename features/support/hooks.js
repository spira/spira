'use strict';

var driver = require('./world.js').getDriver();
var fs = require('fs');
var path = require('path');
var sanitize = require("sanitize-filename");

var myHooks = function () {

    var world = this;

    this.After(function(scenario, callback) {
        if(scenario.isFailed()) {

            var screenshotPath = "logs/integration_screenshots";

            if(!fs.existsSync(screenshotPath)) {
                fs.mkdirSync(screenshotPath);
            }

            this.driver.takeScreenshot().then(function(data){
                var base64Data = data.replace(/^data:image\/png;base64,/,"");

                var filePath = path.join(screenshotPath, sanitize(scenario.getName() +'_'+new Date().toISOString()+ ".png").replace(/ /g,"_"));

                fs.writeFile(filePath, base64Data, 'base64', function(err) {
                    if(err) console.log(err);
                });

            });
        }
        this.driver.manage().deleteAllCookies()
            .then(function() {
                callback();
            });
    });

    this.registerHandler('AfterFeatures', function (event, callback) {
        driver.quit();
        callback();
    });

};

module.exports = myHooks;