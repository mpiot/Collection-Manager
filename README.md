# Collection Manager

Collection Manager is a website intended to manage strains in microbiological laboratories,
it provides the management of:
* Strains (GMO and Wild)
* Plasmids
* Primers

##How to install to develop the app (no adapted for production) ?
These explanations are for install the project under Docker.

1. Install Docker and Docker compose on your computer (see the doc)
2. For use elasticsearch in docker, the vm_map_max_count setting should be set permanently in /etc/sysctl.conf:
    ```
    $ grep vm.max_map_count /etc/sysctl.conf
    vm.max_map_count=262144
    ```
    To apply the setting on a live system type: `sysctl -w vm.max_map_count=262144`
3. `cp docker-compose.yml.dist docker-compose.yml`
4. `docker-compose build`
5. `docker-compose create`
8. The first time, you need to use `docker-compose up -d` to create the Network and Volumes. Next, just use `docker-compose start`
 
The previous steps install nginx, PHP, MariaDb, Elasticsearch in docker containers.
    
1. Set the rights to allow PHP create files (in container www-data user have UID 33):
    ```
    setfacl -R -m u:33:rwX -m u:`whoami`:rwX var/ web/uploads/
    setfacl -dR -m u:33:rwX -m u:`whoami`:rwX var/ web/uploads/
    ```
Enter in the docker container `docker exec -it collection-manager-php bash` to execute commands:
2. Install Vendors
    ```
    composer install
    ```
    Answer to questions in console, all per default, just change secret, and reCaptcha
      * The secret is a 40 random string, you can generate key here: http://nux.net/secret
      * Get Google ReCaptcha keys here: https://www.google.com/recaptcha (Set the correct domaine name when you register)
3. Generate the schema in the Database
    ```
    bin/console doctrine:schema:update --force
    ```
4. Load DataFixtures (example data)
    ```
    bin/console doctrine:fixtures:load
    ```
5. Populate Elasticsearch
    ```
    bin/console fos:elastica:populate
    ```
6. Dump the Assets (CSS/JS)
    ```
    bin/console assetic:dump
    ```
7. Clear the cache
    ```
    bin/console cache:clear
    ```

Any files and folders created by PHP or in the container are root on the host machine. You have to do a chown command each time you want edit files (eg: with the bin/console doctrine:entity).
