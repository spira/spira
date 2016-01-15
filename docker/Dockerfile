# run docker build -t spira/spira:latest -f ./docker/Dockerfile .

FROM busybox:1.24.1

MAINTAINER "Zak Henry" <zak.henry@gmail.com>

RUN mkdir -p /data

# only add the required data code
#COPY api /data/api/
#COPY app/build /data/app/build
#COPY forum /data/forum

# add vhosts for the nginx container
#COPY vhosts /data/vhosts/

COPY . /data

# make sure the logs directory exists
#RUN mkdir -p /data/logs

VOLUME /data

WORKDIR /data