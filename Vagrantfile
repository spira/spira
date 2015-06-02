Vagrant.configure(2) do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname="spira-vagrant"


# webserver
  config.vm.network "forwarded_port", guest: 80, host: 80
  config.vm.network "forwarded_port", guest: 8080, host: 8080
# postgres
  config.vm.network "forwarded_port", guest: 5432, host: 5432
# mailcatcher
  config.vm.network "forwarded_port", guest: 1080, host: 1080
# vm static ip binding
  config.vm.network "private_network", ip: "192.168.2.2"

# mount spira fs
  config.vm.synced_folder "./", "/data", type: "nfs"

# mount docker image repos (if present)
  if File.directory?("../docker")
    config.vm.synced_folder "../docker", "/docker", type: "nfs"
  end


# vm 'physical' config
    config.vm.provider "virtualbox" do |vb|

        # memory
        vb.memory = "4096"
        vb.gui = false
        vb.name = "spira"

    end

# provision with docker
  config.vm.provision :docker
  config.vm.provision :docker_compose

# Provision the box with boostrap file
  config.vm.provision :shell, path: "vagrant/bootstrap.sh"

end
