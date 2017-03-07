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
2. For use elasticsearch in docker, the vm_map_max_count setting should be set permanently in /etc/sysctl.conf:
    ```
    $ grep vm.max_map_count /etc/sysctl.conf
    vm.max_map_count=262144
    ```
    To apply the setting on a live system type: `sysctl -w vm.max_map_count=262144`

3. Build the images:

      ```docker-compose build```

4. Create the containers:

      ```docker-compose create```

5. Create your parameters.yml file: 

      ```cp app/config/parameters.yml.dist app/config/parameters.yml```

6. Edit the parameters.yml, and set scret and reCaptcha keys:
    * The secret is a 40 random string, you can generate key here: http://nux.net/secret
    * Get Google ReCaptcha keys here: https://www.google.com/recaptcha (Set the correct domaine name when you register)

7. The first time, you need to use `docker-compose up -d` to create the Network and Volumes. Next, just use `docker-compose start`
 
The previous steps install nginx, PHP, MariaDb, Elasticsearch in docker containers.
    
Enter in the docker container `docker exec -it collection-manager-php bash` to execute commands:

1. Set the rights in the Docker container:
    ```
    setfacl -R -m u:www-data:rwX -m u:YOUR_UID_ON_HOST:rwX var/ web/uploads/
    setfacl -dR -m u:www-data:rwX -m u:YOUR_UID_ON_HOST:rwX var/ web/uploads/
    ```

2. Install Vendors
    ```
    composer install
    ```

3. Generate the schema in the Database
    ```
    bin/console doctrine:schema:update --force
    ```

4. Load DataFixtures (example data)
    ```
    bin/console doctrine:fixtures:load
    ```

5. Dump the Assets (CSS/JS)
    ```
    bin/console assetic:dump
    ```
6. Clear the cache
    ```
    bin/console cache:clear
    ```
