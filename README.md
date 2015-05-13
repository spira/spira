# Larvae [![Build Status](https://travis-ci.org/xiphiaz/larvae.svg?branch=master)](https://travis-ci.org/xiphiaz/larvae) [![Coverage Status](https://coveralls.io/repos/xiphiaz/larvae/badge.svg?branch=master)](https://coveralls.io/r/xiphiaz/larvae?branch=master)
Laravel + AngularJS project seed



## Docker Notes
### Handy Commands
#### Docker Compose
* `docker-compose up -d` - reads docker-compose.yml and deploys containers
* `docker-compose ps` - list docker containers and their status
* `docker-compose run web` - run an individual container (good for debugging as the errors are output)
* `docker-compose stop` - stop all containers
* `docker-compose run --entrypoint ls web /etc/nginx/sites-available` - run a specific command in container, not the standard entrypoint (process)

#### boot2docker
* `boot2docker up` - start docker host vm
* `boot2docker down` - stop docker host vm
* `boot2docker ssh 'ls -l /data/vhosts/nginx/*.conf'` - execute a command in the host vm
* `VBoxManage sharedfolder add boot2docker-vm --name nglume --hostpath ~/sites/nglume/nglume` - add a shared folder (path to your repo) to the host vm. The name is used for mounting the volume
* `boot2docker ssh 'sudo mount -t vboxsf -o "defaults,uid=33,gid=33,rw" nglume /data'` - mount volume on the host vm (the name must match the shared folder)
* `VBoxManage modifyvm boot2docker-vm --natpf1 "api,tcp,,8080,,8080"` - open a port on the host vm


#### Container development
* `docker build -t nglume/docker-phpfpm:dev .` - build an image, give it a tag
* `docker push nglume/docker-phpfpm:dev` - publish a container back to dockerhub (feel free to halt the process after the first image uploads)


