#!/bin/sh

set -e

BASE_DIR=/metager/metager_app

if [ ! -f "$BASE_DIR/config/sumas.json" ]; then
    cp $BASE_DIR/config/sumas.json.example $BASE_DIR/config/sumas.json
fi

if [ ! -d "$BASE_DIR/database/databases/" ]; then
    mkdir -p "$BASE_DIR/database/databases/"
fi

if [ ! -f "$BASE_DIR/database/databases/database.sqlite" ]; then
    touch "$BASE_DIR/database/databases/database.sqlite"
fi

if [ ! -d "$BASE_DIR/storage/logs/metager" ]; then
    mkdir -p $BASE_DIR/storage/logs/metager
fi