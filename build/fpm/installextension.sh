#!/bin/sh
# Usage: installextension <name> <version> <github owner/repo> [configure args...]
#
# Builds a PHP extension from its release tarball: pecl.php.net first, the same
# tag on GitHub when pecl does not deliver. pecl.php.net has gone down for hours
# at a time (500 and 504 for some packages while others still downloaded), and
# with nothing else to fetch from the image could not be built at all.
#
# This does what `pecl install` does — phpize, configure, make, make install —
# so both sources go through one build and the configure args mean the same
# thing either way. It exits non-zero unless the .so ends up installed, so a
# failed download can never be committed as a layer.

set -eu

NAME="$1"
VERSION="$2"
REPO="$3"
shift 3

SRC="$(mktemp -d)"

if curl -fsSL --retry 3 -o "$SRC/ext.tgz" "https://pecl.php.net/get/${NAME}-${VERSION}.tgz"
then
    echo "${NAME}-${VERSION}: downloaded from pecl.php.net"
else
    >&2 echo "${NAME}-${VERSION}: pecl.php.net failed, falling back to github.com/${REPO}"
    curl -fsSL --retry 3 -o "$SRC/ext.tgz" "https://github.com/${REPO}/archive/refs/tags/${VERSION}.tar.gz"
fi

tar -xzf "$SRC/ext.tgz" -C "$SRC"
# pecl tarballs unpack to <name>-<version>/ next to a package.xml, GitHub's to
# <repo>-<version>/ — either way the sources are the directory with config.m4.
CONFIG_M4="$(find "$SRC" -mindepth 2 -maxdepth 2 -name config.m4 | head -n 1)"
if [ -z "$CONFIG_M4" ]
then
    >&2 echo "ERROR: no config.m4 in the ${NAME}-${VERSION} tarball"
    exit 1
fi

cd "$(dirname "$CONFIG_M4")"
# Packaging a pecl release fills in @PACKAGE_VERSION@; a GitHub tag still has
# the placeholder (imagick would report its version as "@PACKAGE_VERSION@").
grep -rl '@PACKAGE_VERSION@' . | xargs -r sed -i "s/@PACKAGE_VERSION@/${VERSION}/g"
phpize
./configure "$@"
make -j"$(nproc)"
make install
cd /
rm -rf "$SRC"

if [ ! -f "$(php-config --extension-dir)/${NAME}.so" ]
then
    >&2 echo "ERROR: ${NAME}.so was not installed"
    exit 1
fi
