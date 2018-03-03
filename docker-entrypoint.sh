#!/bin/bash

# if parameters.yml, doesn't exists, it's the first container execution
if [ ! -f /var/www/html/app/config/parameters.yml ]; then
    # Create parameters.yml file, to avoid doctrine:migrations test bug
    touch /var/www/html/app/config/parameters.yml

    # Test if the database is ready to handle connections
    statusCode=1
    i=0
    while [ $statusCode -ne 0 ]; do
        i=$[$i+1]
	sleepTime=$(( $i * 5 ))

        if [ $sleepTime -gt 30 ]; then
            set -e
        fi

        bin/console doctrine:migrations:status
        statusCode=$?

        if [ $statusCode -ne 0  ]; then
            echo "Wait $sleepTime seconds..."
            sleep $sleepTime
	fi
    done

    # Exit script if there is an error
    set -e

    # Create log file
    touch var/logs/$SYMFONY_ENV.log

    # Execute scripts
    composer run-script post-install-cmd --no-interaction

    # Only for production
    if [ $SYMFONY_ENV = "prod" ]; then
        # Change owner of files created before
        chown -R www-data:www-data /var/www/html

        # Migrate databases
        bin/console doctrine:migrations:migrate --no-interaction
    fi
fi

exec "$@"
