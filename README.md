# Collection-Manager

### Summary
1. Install the development app
1.1. Install Docker and docker-compose
1.2. Fork the app
1.3. Configure the app
1.4. Install
1.5. Follow the best practice
1.6. How to control your code syntax ?
1.7. Docker images
2. Install the production app

## 1. Install the development app

### 1. Install Docker and docker-compose
The development app use docker and docker-compose, before continue to follow the guide, please install these requirements.
* https://docs.docker.com/install/
* https://docs.docker.com/compose/install/

### 2. Fork the app
1. Fork

    Click on the fork button at the top of the page.

2. Clone your repository (after fork)

        git clone git@github.com:USERNAME/collection-manager.git

3. Create the upstream remote

        cd collection-manager
        git remote add upstream git://github.com/mpiot/collection-manager.git

4. Some infos, to work with upstream and origin remote

    https://symfony.com/doc/current/contributing/code/patches.html

5. Some infos about GitFlow

    https://jeffkreeftmeijer.com/git-flow/

### 3. Configure the app

Now, we will configure the application on your machine, there is 2 files that permit it:
 - parameters.yml: configure credential for db, Google ReCaptcha, SMTP credentials, ...
 - docker-compose.override.ym: configure daemon access like the forwarded ports of nginx to access your app, and db ports
 for debug.
 
        cp /app/parameters.yml.dist /app/parameters.yml
        vi /app/parameters.yml
    
        cp docker-compose.override.yml.dist docker-compose.override.yml
        vi docker-compose.override.yml

### 4. Install

That's finish in a few time, now, just execute:

    make install
    
And voilà !!! Your app is installed and ready to use.

### 5. Follow the best practice
There is a **beautiful** guide about the best practice :) You can find it on the [Symfony Documentation - Best Practice](http://symfony.com/doc/current/best_practices/index.html).

### 6. How to control your code syntax ?
For a better structure of the code, we use Coding standards: PSR-0, PSR-1, PSR-2 and PSR-4.
You can found some informations on [the synfony documentation page](http://symfony.com/doc/current/contributing/code/standards.html).

In the project you have a php-cs-fixer.phar file, [the program's documentation](http://cs.sensiolabs.org/).

Some commands:
   * List PHP files with mistakes

    make php-cs

   * Fix PHP files:

    make php-cs-fix

   * List config and twig files with mistakes
   
    make lint-symfony

### 7. Docker images
The docker images are automatically created, when a commit is done on the develop branch, a dev images was created. When
a tag is added on a commit on the master branch, the production images was created.

Images models:
  * dev: the latest dev image
  * dev-hash: dev image for a specific docker folder
  * latest: the latest prod image
  * x.y.z: specific prod image

## 2. Production

Available production images on: https://hub.docker.com/r/mapiot/collection-manager/
  * latest: the latest prod image
  * x.y.z: specific prod image

### 2.1 First install

Copy required files from GitHub for example:

  * docker-compose.eg-prod.yml to docker-compose.yml
  * docker folder/common folder (respect path)

Configure the environments in docker-compose.yml file, and check the version in files is the last of CollectionManager.

Execute commands:

    docker-compose up -d
    docker-compose exec app bin/console doctrine:migrations:migrate
    docker-compose exec app bin/console fos:elastica:populate

Finished :-) You can configure a ReverseProxy to set a domain name for example (HaProxy).

### 2.2. Update

First, it's a good idea to dump database and save data uploaded by users:
    
    # Init filenames
    DATE=$(date +"%Y-%m-%d_%H-%M-%S")
    DB_BACKUP=$DATE.sql
    FILES_BACKUP=$DATE.tar.gz

    # Backup MySQL
    docker-compose exec db /usr/bin/mysqldump -u root --password=PASSWORD DATABASE > $DB_BACKUP
    
    # Backup files
    docker run --rm --volumes-from CONTAINER_NAME -v $(pwd):/backup ubuntu tar -P -czf /backup/$FILES_BACKUP /app/files >> /dev/null

If you need to restore the db:
    
    # Restore MySQL
    cat backup.sql | docker-compose exec app /usr/bin/mysql -u root --password=PASSWORD DATABASE

Edit the docker-compose.yml file by changing the version of CollectionManager

Stop and remove app and nginx containers (now, the service is interrupted):

    docker-compose stop app nginx
    docker-compose rm app nginx

Remove the source_code volume (this volume permit to share the application source code between app and nginx):

    # List volumes
    docker volume ls
    
    # Delete volume
    docker volume rm FolderNameWhereDockerComposeFileIs_app_source_code

Re-create and start containers:

    docker-compose up -d app nginx

Migrate database to the new schema, and re-populate Elasticsearch index (only if needed, see changelog.md)

    docker-compose exec app bin/console doctrine:migrations:migrate
    docker-compose exec app bin/console fos:elastica:populate
