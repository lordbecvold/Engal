#!/bin/bash

red_echo () { echo "\033[31m\033[1m$1\033[0m"; }

# clear frontend files
red_echo "[Clear]: clearing frontend files"
sudo rm -rf ./build
sudo rm -rf ./node_modules
sudo rm ./package-lock.json
