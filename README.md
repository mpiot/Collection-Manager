# Collection Manager

Collection Manager is a website intended to manage strains in microbiological laboratories,
it provides the management of:
* GMO strains
* Wild strains
* Plasmids
* Primers
 
 ##How to install to develop the app (no adapted for production) ?
 These explanations are for install the project under Docker.

 1. Install Docker and Docker compose on your computer (see the doc)
 2. Build the images: `docker-compose build`
 3. Create the containers: `docker-compose create`
 4. The first time, you need to use `docker-compose up -d` to create the Network. Next, juste use `docker-compose start`
 
 The prevous steps install nginx, PHP, MariaDb, ElasticSearch in docker containers.
    
 Enter in the docker container `docker exec -it CONTAINER_NAME bash` to execute commands:
 
     composer install
     bin/console doctrine:schema:update --force
     bin/console doctrine:fixtures:load
     bin/console assetic:dump
     bin/console cache:clear
 
 Now, you can start to work on the app.
