# ng-lume [![Build Status](https://travis-ci.org/nglume/nglume.svg?branch=master)](https://travis-ci.org/nglume/nglume) [![Coverage Status](https://coveralls.io/repos/nglume/nglume/badge.svg?branch=master)](https://coveralls.io/r/nglume/nglume?branch=master)
Lumen + AngularJS project seed with Docker

## Docker setup
## Install boot2docker.
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

## Share & mount your dev folder with the vm
Note that it is important for the containers to work to mount to the repo root (with this README as a child) as some of the containers rely on the folder structure 

```sh
$ VBoxManage sharedfolder add boot2docker-vm --name nglume --hostpath /path/to/your/site/repo
```
## Start boot2docker

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

## Set your host entries to point to the vm
Try the one liner below (check the values are what you are wanting):

```
sudo -- sh -c "printf '\n\n#nglume docker\n192.168.59.103\tlocal.nglume.io\n192.168.59.103\tlocal.api.nglume.io\n192.168.59.103\tlocal.app.nglume.io' >> /etc/hosts"
```

## Mount the shared folder on the vm at the location /data (this is important, the main docker-data container config relies on this location)

```sh
$ boot2docker ssh 'sudo mkdir /data'
$ boot2docker ssh 'sudo mount -t vboxsf -o "defaults,uid=33,gid=33,rw" nglume /data'
```

Verify the project files are mounted with 

```sh
$ boot2docker ssh 'ls -l /data'
```

## Open up all the necessary ports for development. (Stop the vm first)

```sh
$ boot2docker down
$ VBoxManage modifyvm boot2docker-vm --natpf1 "web,tcp,,80,,80"
$ VBoxManage modifyvm boot2docker-vm --natpf1 "api,tcp,,8080,,8080"
$ VBoxManage modifyvm boot2docker-vm --natpf1 "dockerssh,tcp,,42222,,42222"
```

## Start the vm and start all containers in background

```sh
$ boot2docker up
$ docker-compose up -d
```

## Check on the containers status

```sh
$ docker-compose ps
```

## Build the database

```sh
$ docker-compose run artisan migrate --seed
```

All containers should have either exited 0 or be running

## Open the webpage in your browser

MacOS command for the lazy:

```sh
open -a "Google Chrome" http://local.app.nglume.io
```


This initial build will take some time as all the containers need to be downloaded, however they are cached and each reboot pulls from cache.

## SSH connection

If you wish to connect to the container via SSH (eg to connect to the database from a client), you can use a connection made available in the ssh container.

```
$ ssh root@local.nglume.io -p 42222
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

#### boot2docker
* `boot2docker up` - start docker host vm
* `boot2docker down` - stop docker host vm
* `boot2docker ssh 'ls -l /data/vhosts/nginx/*.conf'` - execute a command in the host vm
* `VBoxManage sharedfolder add boot2docker-vm --name nglume --hostpath ~/sites/nglume/nglume` - add a shared folder (path to your repo) to the host vm. The name is used for mounting the volume
* `boot2docker ssh 'sudo mount -t vboxsf -o "defaults,uid=33,gid=33,rw" nglume /data'` - mount volume on the host vm (the name must match the shared folder)
* `boot2docker ssh 'ls -l /data'` - verify mounting in boot2docker
* `VBoxManage modifyvm boot2docker-vm --natpf1 "api,tcp,,8080,,8080"` - open a port on the host vm
* `VBoxManage modifyvm "boot2docker-vm" --natpf1 delete "xdebug"` - close opened port


#### Container development
* `docker build -t nglume/docker-phpfpm:latest .` - build an image, give it a tag
* `docker push nglume/docker-phpfpm:latest` - publish a container back to dockerhub (feel free to halt the process after the first image uploads, the process continues in the background)

## Deployment Notes
* For security XDEBUG_ENABLED should NOT be set to true in production - the way xdebug is configured for docker allows for remote connection from any host.
