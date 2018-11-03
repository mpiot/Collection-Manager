#!/bin/bash

VERSION=$1

sed -i -E "s/(APP_VERSION=)[0-9\.]+/\1${VERSION}/g" Dockerfile
sed -i -E "s/(mapiot\/collection-manager:)[0-9\.]+/\1${VERSION}/g" docker-compose.prod.yml

git add Dockerfile docker-compose.prod.yml
git commit -m "Bump version to: $VERSION"
