#!/bin/bash

docker exec -it autom8-web vendor/bin/phinx rollback -c bootstrap/phinx.yml