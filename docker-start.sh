#!/bin/bash

echo "copying source files to volume"

#rm -rf /data/.* #delete data dir
cp -r /src/* /data #copy over all source files
