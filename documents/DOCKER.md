# Docker Notes
## Handy Commands
### Docker Compose

* `docker-compose up -d` - reads docker-compose.yml and deploys containers
* `docker-compose ps` - list docker containers and their status
* `docker-compose run web` - run an individual container (good for debugging as the errors are output)
* `docker-compose stop` - stop all containers
* `docker-compose pull` - re-pull all containers from the registry (checking for updates)
* `docker-compose run --entrypoint ls web /etc/nginx/sites-available` - run a specific command in container, not the standard entrypoint (process)
* `docker inspect --format '{{ .NetworkSettings.IPAddress }}' data_db_1` - get IP address of a container


### vagrant handy commands
* `vagrant ssh` - log into vagrantbox

### boot2docker handy commands
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

### Container development (only available from within the spira vagrant box if you place docker source repos in a folder (../docker) relative to this README.md file.
* `docker build -t spira/docker-phpfpm:latest .` - build an image, give it a tag
* `docker push spira/docker-phpfpm` - publish a container back to dockerhub (feel free to halt the process after the first image uploads, the process continues in the background). Also note that unless pushing to a private hub, you are better off not pushing from local, and use [Docker Hub's Automated Builds](https://docs.docker.com/docker-hub/builds/) instead.
* `docker images -q --filter "dangling=true" | xargs docker rmi` - delete all images that are not current (free up some disk space)
* Getting resolve issues when building? Edit `/etc/resolve.conf` in boot2docker. (`boot2docker ssh` then `vi /etc/resolve.conf` to edit) and change the nameserver entry: `nameserver 8.8.8.8`