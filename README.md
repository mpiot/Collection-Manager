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

## 2. Install the production app

To install the prod version, you just have to use the production image available on: https://hub.docker.com/r/mapiot/collection-manager/
  * latest: the latest prod image
  * x.y.z: specific prod image

docker-compose.yml example (used files are in docker folder):

    version: '3.2'
    
    services:
        nginx:
            build: docker/prod/nginx
            depends_on:
                - app
            networks:
                - frontend
            volume_from:
                - app
    
        app:
            image: mapiot/collection-manager:x.y.z
            environment:
                - DATABASE_NAME=collection-manager
                - DATABASE_HOST=db
                - DATABASE_PORT=3306
                - DATABASE_USER=collection-manager
                - DATABASE_PASSWORD=collection-manager
                - ELASTICSEARCH_HOST=es1
                - ELASTICSEARCH_PORT=9200
                - SMTP_HOST=host
                - SMTP_PORT=587
                - SMTP_USER=login
                - SMTP_PASSWORD=password
                - MAILER_SENDER_ADDRESS=cme@myhost.tld
                - MAILER_SENDER_NAME='Collection Manager'
                - SYMFONY_SECRET=ThisTokenIsNotSoSecretChangeIt
                - RECAPTCHA_PUBLIC_KEY=ReplaceWithYourOwnReCaptchaPublicKeyForCatcha
                - RECAPTCHA_PRIVATE_KEY=ReplaceWithYourOwnReCaptchaPrivateKeyForCatcha
            depends_on:
                - redis
                - rabbitmq
                - db
                - es1
            networks:
                - frontend
                - backend
            volumes:
                - app_data:/app/files
    
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
            build: ./docker/common/elasticsearch
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
            build: ./docker/common/elasticsearch
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
    
        rabbitmq:
            image: rabbitmq:3.7.3
            environment:
                - RABBITMQ_DEFAULT_USER=collection-manager
                - RABBITMQ_DEFAULT_PASS=collection-manager
            volumes:
                - rabbitmq_data:/var/lib/rabbitmq
            networks:
                - backend
    
        redis:
            image: redis:4.0.8
            volumes:
                - redis_data:/data
            networks:
                - backend
    
    volumes:
        app_data:
            driver: local
        db_data:
            driver: local
        es1_data:
            driver: local
        es2_data:
            driver: local
        rabbitmq_data:
            driver: local
        redis_data:
            driver: local
    
    networks:
        frontend:
        backend:
