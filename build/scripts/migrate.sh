#!/bin/bash

docker exec -it autom8-web vendor/bin/phinx migrate -c bootstrap/phinx.yml