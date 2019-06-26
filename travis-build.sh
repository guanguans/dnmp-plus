#!/usr/bin/env bash

#### halt script on error
set -xe

echo '##### Print docker version'
docker --version

echo '##### Print environment'
env | sort

#### Build the Docker Images

cp env.sample .env
cat .env
cp docker-compose-sample.yml docker-compose.yml
docker-compose up -d
docker images
