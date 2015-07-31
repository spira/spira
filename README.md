# Spira 
Lumen + AngularJS project seed with Docker

[![Build Status](https://travis-ci.org/spira/spira.svg?branch=master)](https://travis-ci.org/spira/spira) 
[![Coverage Status](https://coveralls.io/repos/spira/spira/badge.svg?branch=master)](https://coveralls.io/r/spira/spira?branch=master)
[![Dependency Status](https://gemnasium.com/spira/spira.svg)](https://gemnasium.com/spira/spira)
[![Code Climate](https://codeclimate.com/github/spira/spira/badges/gpa.svg)](https://codeclimate.com/github/spira/spira)
[![StyleCI](https://styleci.io/repos/35469223/shield)](https://styleci.io/repos/35469223)

## Technologies integrated in this seed project
* [Lumen](http://lumen.laravel.com/)
* [HHVM](http://hhvm.com/)
* [Xdebug](http://xdebug.org/)
* [AngularJS](https://angularjs.org/) (1.x)
* [Docker](https://www.docker.com/)
* [Vagrant](http://docs.vagrantup.com/v2/provisioning/docker.html)
* [Docker compose](https://docs.docker.com/compose/)
* [Gulp](http://gulpjs.com/)
* [BrowserSync](http://www.browsersync.io/)
* [PhantomJS](http://phantomjs.org/)
* [Karma](http://karma-runner.github.io/)
* [Bower](http://bower.io/)
* [Typescript](http://www.typescriptlang.org/)
* [API Blueprint](https://apiblueprint.org/)
  * [Apiary](https://apiary.io/)
  * [Drakov](https://github.com/Aconex/drakov)
  * [Snowcrash](https://github.com/apiaryio/snowcrash)
* [Clarity](https://github.com/tobi/clarity)
* [Mailcatcher](http://mailcatcher.me/)
* [Beanstalkd](https://github.com/kr/beanstalkd)
* [Redis](http://redis.io/)

## Setup Instructions
Spira has a yeoman generator at https://github.com/spira/generator-spira which is the easiest way to set up the project. To set up the project, do the following:

1. Install [npm](https://www.npmjs.com/).
2. Ensure that you have added an authorise token from your github account. Part way through the composer install section you may get rate limited by github. See instructions [here](https://help.github.com/articles/creating-an-access-token-for-command-line-use/).
3. Install yeoman:
  
  ```
  $ npm install -g yo
  ```
4. Install spira's yeoman generator:
 
  ```
  $ npm install -g generator-spira
  ```
5. Navigate to a directory where you want the project to be installed and run the following command:
 
  ```
  $ yo spira
  ```
6. Follow the on screen instructions and start the generator by pressing enter. Note you may have to enter your sudo password a few times during install:
  * To edit /etc/exports for NAT
  * To edit /etc/hosts for host resolving

## Adding Type Definitions

When adding type definitions the build process may be interrupted due to the Github API rate-limit. In the case of this happening you will need to create another Github token and allow the TSD library to access it. For more information on how to do this please see the [TSD documentation](https://github.com/DefinitelyTyped/tsd). 

## Manual Setup Instructions
You should be able to use the yeoman generator to install this project but if you have any issues consulting the [manual setup instructions](documents/MANUALSETUP.md) may be of use.

## Docker Notes
For more information regarding docker, please see our [docker notes](documents/DOCKER.md).
