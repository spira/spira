# Spira 
Lumen + AngularJS project seed with Docker

[![Build Status](https://travis-ci.org/spira/spira.svg?branch=master)](https://travis-ci.org/spira/spira) [![Coverage Status](https://coveralls.io/repos/spira/spira/badge.svg?branch=master)](https://coveralls.io/r/spira/spira?branch=master)
[![Dependency Status](https://gemnasium.com/spira/spira.svg)](https://gemnasium.com/spira/spira)


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
* [Postman](https://www.getpostman.com/)
 * [Newman](https://github.com/a85/Newman) 



## Docker setup

You have two options - boot2docker and vagrant. Vagrant is the recommended setup, however you may already have boot2docker working and prefer it. Instructions for setup below.
Vagrant is recommended as it supports NFS folder sharing in a simple way, whereas boot2docker takes a significant amount more work to set up (outside of the instructions below).

## Vagrant setup

### Install vagrant
Vagrant manages provisioning of virtual machines
See [http://docs.vagrantup.com/v2/installation](http://docs.vagrantup.com/v2/installation/)

### Install virtualbox or vmware
virtualbox & vmware are both virtual machine runners
virtualbox is free at [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)

### Start vagrant machine
```sh
vagrant up
```

### Add host entries to /etc/hosts
```
sudo -- sh -c "printf '\n\n#spira docker\n192.168.2.2\tlocal.spira.io\n192.168.2.2\tlocal.api.spira.io\n192.168.2.2\tlocal.app.spira.io' >> /etc/hosts"
```

### Log into vagrant box
On login you will see the output of `docker-compose ps` which gives you the status of all containers 
```sh
vagrant ssh
```

### Check file system is mounted in current work dir

```sh
ls -lah
```

### Initialise the database
```sh
docker-compose run artisan migrate --seed
```

### Open the webpage in your browser

MacOS command for the lazy:

```sh
open -a "Google Chrome" http://local.app.spira.io
```

## boot2docker setup
boot2docker is a lightweight linux distro that runs entirely in ram, only for running docker containers. 
It also puts the `docker` command on your system path to allow you to create and publish docker containers, and orchestrate multiple containers with `docker-compose`

Note: we don't yet use [kitematic](http://kitematic.com) as it does not (yet) support orchestration of multiple containers with docker-compose

Follow the install steps for your relevant OS. [https://github.com/boot2docker/boot2docker](https://github.com/boot2docker/boot2docker)

If using a linux variant as your dev os, just install docker - there is no need for a docker runner, however the path mapping defined in docker-compose might not be correct for you.
Consider establishing a common location for all of your developers, for example /var/www/<projectkey> and symlink from to that location.

## Initialise boot2docker

```sh
$ boot2docker init
```

### Share & mount your dev folder with the vm
Note that it is important for the containers to work to mount to the repo root (with this README as a child) as some of the containers rely on the folder structure 

```sh
$ VBoxManage sharedfolder add boot2docker-vm --name spira --hostpath /path/to/your/site/repo
```
### Start boot2docker

```sh
$ boot2docker up
```

Note that on first start boot2docker will output 3 exports that you must run eg:

```
To connect the Docker client to the Docker daemon, please set:
    export DOCKER_HOST=tcp://192.168.59.103:2376
    export DOCKER_CERT_PATH=/Users/zak/.boot2docker/certs/boot2docker-vm
    export DOCKER_TLS_VERIFY=1
```

You must either run the commands in your shell, or copy them to your .bashrc file to have them set every time

### Set your host entries to point to the vm
Try the one liner below (check the values are what you are wanting):

```
sudo -- sh -c "printf '\n\n#spira docker\n192.168.59.103\tlocal.spira.io\n192.168.59.103\tlocal.api.spira.io\n192.168.59.103\tlocal.app.spira.io' >> /etc/hosts"
```

### Mount the shared folder on the vm at the location /data (this is important, the main docker-data container config relies on this location)

```sh
$ boot2docker ssh 'sudo mkdir /data'
$ boot2docker ssh 'sudo mount -t vboxsf -o uid=1000,gid=50 spira /data'
```

Verify the project files are mounted with 

```sh
$ boot2docker ssh 'ls -l /data'
```

### Open up all the necessary ports for development. (Stop the vm first)

```sh
$ boot2docker down
$ VBoxManage modifyvm boot2docker-vm --natpf1 "web,tcp,,80,,80"
$ VBoxManage modifyvm boot2docker-vm --natpf1 "api,tcp,,8080,,8080"
$ VBoxManage modifyvm boot2docker-vm --natpf1 "dockerssh,tcp,,42222,,42222"
$ VBoxManage modifyvm boot2docker-vm --natpf1 "mailcatcher,tcp,,1080,,1080"
```

### Start the vm and start all containers in background

```sh
$ boot2docker up
$ docker-compose up -d
```

### Check on the containers status

```sh
$ docker-compose ps
```

### Grant webserver permissions for storage folder

```sh
$ chmod -R 777 api/storage/
```

### Build the database

```sh
$ docker-compose run artisan migrate --seed
```

All containers should have either exited 0 or be running

### Open the webpage in your browser

MacOS command for the lazy:

```sh
open -a "Google Chrome" http://local.app.spira.io
```


This initial build will take some time as all the containers need to be downloaded, however they are cached and each reboot pulls from cache.

### SSH connection

If you wish to connect to the container via SSH (eg to connect to the database from a client), you can use a connection made available in the ssh container.

```
$ ssh root@local.spira.io -p 42222
```

Note that the port is 42222. This is to avoid collision with the connection to the boot2docker vm.

## Docker Notes
### Handy Commands
#### Docker Compose

* `docker-compose up -d` - reads docker-compose.yml and deploys containers
* `docker-compose ps` - list docker containers and their status
* `docker-compose run web` - run an individual container (good for debugging as the errors are output)
* `docker-compose stop` - stop all containers
* `docker-compose pull` - re-pull all containers from the registry (checking for updates)
* `docker-compose run --entrypoint ls web /etc/nginx/sites-available` - run a specific command in container, not the standard entrypoint (process)
* `docker inspect --format '{{ .NetworkSettings.IPAddress }}' data_db_1` - get IP address of a container


#### vagrant handy commands
* `vagrant ssh` - log into vagrantbox

#### boot2docker handy commands
* `boot2docker up` - start docker host vm
* `boot2docker down` - stop docker host vm
* `boot2docker ssh 'ls -l /data/vhosts/nginx/*.conf'` - execute a command in the host vm
* `VBoxManage sharedfolder add boot2docker-vm --name spira --hostpath ~/sites/spira/spira` - add a shared folder (path to your repo) to the host vm. The name is used for mounting the volume
* `VBoxManage setextradata boot2docker-vm VBoxInternal2/SharedFoldersEnableSymlinksCreate/spira 1` - allow symlinking within the shared volume
* `boot2docker ssh 'sudo mount -t vboxsf -o uid=1000,gid=50 spira /data'` - mount volume on the host vm (the name must match the shared folder)
* `boot2docker ssh 'ls -l /data'` - verify mounting in boot2docker
* `VBoxManage modifyvm boot2docker-vm --natpf1 "api,tcp,,8080,,8080"` - open a port on the host vm
* `VBoxManage modifyvm "boot2docker-vm" --natpf1 delete "xdebug"` - close opened port
* `VBoxManage modifyvm boot2docker-vm --memory 4000` - allocate more ram to the machine (unit is MB)



#### Container development (not available from within the spira vagrant box)
* `docker build -t spira/docker-phpfpm:latest .` - build an image, give it a tag
* `docker push spira/docker-phpfpm` - publish a container back to dockerhub (feel free to halt the process after the first image uploads, the process continues in the background)
* `docker images -q --filter "dangling=true" | xargs docker rmi` - delete all images that are not current (free up some disk space)
* Getting resolve issues when building? Edit `/etc/resolve.conf` in boot2docker. (`boot2docker ssh` then `vi /etc/resolve.conf` to edit) and change the nameserver entry: `nameserver 8.8.8.8`

## Deployment Notes
* For security XDEBUG_ENABLED should NOT be set to true in production - the way xdebug is configured for docker allows for remote connection from any host.
