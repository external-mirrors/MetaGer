#!/bin/sh

set -e

NODE_VERSION=v14.17.5
NODE_DISTRO=linux-x64
NODE_URL="https://nodejs.org/dist/$NODE_VERSION/node-$NODE_VERSION-$NODE_DISTRO.tar.xz"
# Download Nodejs archive
curl -o /tmp/node-$NODE_VERSION-$NODE_DISTRO.tar.xz "$NODE_URL"

tar -xJvf /tmp/node-$NODE_VERSION-$NODE_DISTRO.tar.xz -C /usr/local/lib
mv /usr/local/lib/node-$NODE_VERSION-$NODE_DISTRO /usr/local/lib/nodejs
rm /tmp/node-$NODE_VERSION-$NODE_DISTRO.tar.xz

echo "export PATH=/usr/local/lib/nodejs/bin:$PATH" >> ~/.profile
. ~/.profile