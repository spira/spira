# run `docker build -t spira/spira:latest .` to build this container

FROM spira/docker-base

MAINTAINER "Zak Henry" <zak.henry@gmail.com>


RUN mkdir -p /build

# only add the required build code
COPY api /build/api/
COPY app/build /build/app/build

# add vhosts for the nginx container
COPY vhosts /build/vhosts/

# make sure the logs directory exists
RUN mkdir -p /build/logs

RUN pwd && ls -al /build && ls -al /build/vhosts/nginx