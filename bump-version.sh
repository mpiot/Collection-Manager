#!/bin/bash

VERSION=$1

sed -i -E "s/(APP_VERSION=)[0-9\.]+/\1${VERSION}/g" Dockerfile docker/AppDevDockerfile
sed -i -E "s/(mapiot\/collection-manager:)[0-9\.]+/\1${VERSION}/g" docker-compose.prod.yml

git add Dockerfile docker/AppDevDockerfile docker-compose.prod.yml
git commit -m "Bump version to: $VERSION"
