#!/bin/bash

cat >> /home/vagrant/.bashrc <<EOL
# Bashrc added by docker provisioner ./vagrant/.bashrcappend

cd /data
docker-compose ps
EOL