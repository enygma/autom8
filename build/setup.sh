#!/bin/bash

docker build -t autom8-api -f Dockerfile-api .
docker build -t autom8-web -f Dockerfile-web .
docker-compose up --remove-orphans