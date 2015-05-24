Vagrant.configure(2) do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname="nglume-vagrant"

  config.vm.provision :docker
  config.vm.provision :docker_compose, yml: "/data/docker-compose.yml", run: "always"

# webserver
  config.vm.network "forwarded_port", guest: 80, host: 80
  config.vm.network "forwarded_port", guest: 8080, host: 8080
# postgres
  config.vm.network "forwarded_port", guest: 5432, host: 5432
# mailcatcher
  config.vm.network "forwarded_port", guest: 1080, host: 1080
# vm static ip binding
  config.vm.network "private_network", ip: "192.168.2.2"

# mount folder everything 777 so lumen wont complain about permissions
  config.vm.synced_folder "./", "/data", type: "nfs"

# vm 'physical' config
    config.vm.provider "virtualbox" do |vb|

        # memory
        vb.memory = "4096"

    end

# Provision the box with boostrap file
#  config.vm.provision :shell, path: "vagrant/bootstrap.sh"

end
