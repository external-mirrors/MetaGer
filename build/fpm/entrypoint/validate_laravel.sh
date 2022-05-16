#!/bin/sh

set -e

BASE_DIR=/metager/metager_app

if [ ! -f "$BASE_DIR/config/sumas.json" ]; then
    cp $BASE_DIR/config/sumas.json.example $BASE_DIR/config/sumas.json
fi

if [ ! -f "$BASE_DIR/config/sumasEn.json" ]; then
    cp $BASE_DIR/config/sumas.json.example $BASE_DIR/config/sumasEn.json
fi

if [ ! -f "$BASE_DIR/database/database.sqlite" ]; then
    touch $BASE_DIR/database/database.sqlite
fi

if [ ! -d "$BASE_DIR/storage/logs/metager" ]; then
    mkdir -p $BASE_DIR/storage/logs/metager
fi