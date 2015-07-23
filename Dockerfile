# run `docker build -t spira/spira:latest .` to build this container

FROM spira/docker-base

MAINTAINER "Zak Henry" <zak.henry@gmail.com>

RUN mkdir -p /data

# only add the required data code
COPY api /data/api/
COPY app/build /data/app/build

# add vhosts for the nginx container
COPY vhosts /data/vhosts/

# make sure the logs directory exists
RUN mkdir -p /data/logs

VOLUME /data

WORKDIR /data