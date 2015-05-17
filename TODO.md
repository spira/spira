#TODO

## Project
* Extend readme
* Add apiary documentation for API

## Gulpfile
* Add file watchers to handle file changes
* Add test runner watching for karma
* Create docker container for running gulp commands, add rewrite to bashrc? and readme ie `docker-compose run gulp [gulp command]`
* Add host-only gulp tasks (work out how to differentiate in gulpfile)
** Add boot2docker tasks `gulp vm:[up|restart|down|init]`
** Add docker-compose tasks `gulp containers:[up|restart|down|status|pull]`
* Make php coverage reporting use docker container `docker-compose run --entrypoint /data/api/vendor/bin/phpunit php --coverage-clover=/data/reports/coverage/api/clover.xml --colors -c /data/api/phpunit.xml`

## APP
* Migrate from bootstrap to foundation

## API
* Code coverage generation
* Add JWT authentication
* Disable sessions (completely)
* Add email
* Add queue running (use email above)

## Travis
* Make travis use docker deployment ([not yet possible](http://blog.travis-ci.com/2014-12-17-faster-builds-with-container-based-infrastructure/))

## Docker
* Configure gulp task to have php cli so it can run phpunit tests
* Install phantomjs binary in gulp image so karma-phantomjs-launcher doesnt try to install it (currently failing)
** Consider trying phantomjs 2.0