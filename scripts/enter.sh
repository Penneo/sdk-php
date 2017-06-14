#!/bin/bash
#
# Login to a running container

# Make sure that the working directory is the app root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../"

DOCKER_ENV=${1:-"dev"}

source docker/.env

echo
echo 'Docker Image Name :' $DOCKER_IMAGE_NAME
echo 'Environment       :' $DOCKER_ENV
echo

CONTAINER_ID=$(docker ps | grep ${DOCKER_IMAGE_NAME} | grep ${DOCKER_ENV} | awk '{print $1}')

if [ -n "$CONTAINER_ID" ]; then
    docker exec -it $CONTAINER_ID bash
else
    echo "No running container found for environment: $DOCKER_ENV"
    echo
    exit -1
fi

