# Collection-Manager

### Summary
1. Install the development app(#Install the development app)
1.1. Install Docker and docker-compose
1.2. Fork the app
1.3. Configure the app
1.4. Install
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

### 7. Docker images
The docker images are automatically created, when a PR is merged in the develop branch, a dev images is created. When
a tag is added on a merge commit in the master branch, the production images was created.

Images models:
  * mapiot/collection-manager-dev:latest: the latest dev image
  * mapiot/collection-manager-dev:hash: dev image for a specific docker folder
  * mapiot/collection-manager:latest: the latest prod image
  * mapiot/collection-manager-dev:x.y.z: specific prod image
        * x: major version
        * y: feature version
        * z: hotfix/bugfix version

## 2. Production

Available production images are on: https://hub.docker.com/r/mapiot/collection-manager/
  * latest: the latest prod image
  * x.y.z: specific prod image

### 2.1 First install

In this documentation we will explain how to install Collection-Manager with docker compose. Then the first step is 
the installation of docker and docker-compose (check the official doc of this tools).

Then, you have to create a `docker-compose.yml` file, an example below:

```yaml
version: '3.4'

services:
    nginx:
        build:
            context: docker
            dockerfile: NginxDockerfile
        depends_on:
            - app
        networks:
            - frontend
        ports:
            - 127.0.0.1:8080:80
        volumes:
            - app_source_code:/app
            - app_data:/app/files

    app:
        image: mapiot/collection-manager:0.2.2
        environment:
            - MAILER_SENDER_ADDRESS=name@domain.tld
            - MAILER_SENDER_NAME=Collection-Manager
            - APP_SECRET=cc20ed8408adabbf1f8b2ff82940b5c7
            - DATABASE_URL=mysql://collection-manager:collection-manager@db:3306/collection-manager
            - ELASTICSEARCH_HOST=es1
            - ELASTICSEARCH_PORT=9200
            - MAILER_URL=null://localhost
            - GOOGLE_RECAPTCHA_SITE_KEY=ReplaceWithYourOwnReCaptchaPublicKey
            - GOOGLE_RECAPTCHA_SECRET=ReplaceWithYourOwnReCaptchaPrivateKey
        depends_on:
            - db
            - es1
        networks:
            - frontend
            - backend
        volumes:
            - app_source_code:/app
            - app_data:/app/files
            - app_sessions:/app/var/sessions

    db:
        image: mysql:5.7.21
        environment:
          - MYSQL_ROOT_PASSWORD=collection-manager
          - MYSQL_USER=collection-manager
          - MYSQL_PASSWORD=collection-manager
          - MYSQL_DATABASE=collection-manager
        volumes:
            - db_data:/var/lib/mysql
        networks:
            - backend

    es1:
        build:
            context: docker
            dockerfile: ElasticsearchDockerfile
        environment:
            - cluster.name=collection-manager-cluster
            - bootstrap.memory_lock=true
            - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
        ulimits:
            memlock:
                soft: -1
                hard: -1
        volumes:
            - es1_data:/usr/share/elasticsearch/data
        networks:
            - backend

    es2:
        build:
            context: docker
            dockerfile: ElasticsearchDockerfile
        environment:
            - cluster.name=collection-manager-cluster
            - bootstrap.memory_lock=true
            - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
            - "discovery.zen.ping.unicast.hosts=es1"
        ulimits:
            memlock:
                soft: -1
                hard: -1
        volumes:
            - es2_data:/usr/share/elasticsearch/data
        networks:
            - backend

volumes:
    app_source_code:
    app_data:
    app_sessions:
    db_data:
    es1_data:
    es2_data:

networks:
    frontend:
    backend:

```

Configure the environments in `docker-compose.yml` file, and check the version in files is the last of Collection-Manager.

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
