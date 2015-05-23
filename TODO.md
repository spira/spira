#TODO

## Project
* Extend readme
* Add apiary documentation for API

## Gulpfile
* Add file watchers to handle file changes \#13
* Add test runner watching for karma \#13
* Create docker container for running gulp commands, add rewrite to bashrc? and readme ie `docker-compose run gulp [gulp command]` \#14
* Add host-only gulp tasks (work out how to differentiate in gulpfile) \#14
** Add boot2docker tasks `gulp vm:[up|restart|down|init]` \#14
** Add docker-compose tasks `gulp containers:[up|restart|down|status|pull]` \#14
* Make php coverage reporting use docker container `docker-compose run --entrypoint /data/api/vendor/bin/phpunit php --coverage-clover=/data/reports/coverage/api/clover.xml --colors -c /data/api/phpunit.xml`

## API
* Code coverage generation
* Add JWT authentication
* Disable sessions (completely)

## Travis
* Make travis use docker deployment ([not yet possible](http://blog.travis-ci.com/2014-12-17-faster-builds-with-container-based-infrastructure/))

## Docker
* Configure gulp task to have php cli so it can run phpunit tests
