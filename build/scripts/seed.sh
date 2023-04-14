#!/bin/bash

docker exec -it autom8-web vendor/bin/phinx seed:run -c bootstrap/phinx.yml