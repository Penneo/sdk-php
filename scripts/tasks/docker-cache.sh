#!/bin/bash

# Stuff needed for pretty printing
bold=$(tput bold)
normal=$(tput sgr0)

# Make sure that the working directory is the app root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../../"

if [[ "$#" -lt 1 ]]; then
    ENVS=( $(find docker/* -name packer.json -print0 | xargs -0 -n1 dirname | sort --unique | cut -sd / -f 2-) )
    echo "Usage: docker_cache.sh [environment]"
    echo "Available targets: ${bold}$(printf "%s " "${ENVS[@]}")${normal}"
    exit 1
fi

if [ ! -f "docker/.env" ]; then
    echo ".env file is missing in the docker directory."
    exit 1
fi

source docker/.env

DOCKER_ENV=$1
# Determine the current version of the requested runtime environment
VERSION_VAR=$`echo ${DOCKER_ENV}| tr "[:lower:]" "[:upper:]"`_VERSION
VERSION=$(eval "echo $VERSION_VAR")

IMAGE_BACKUP_FILE="docker/cache/${DOCKER_ENV}_${VERSION}.tar.gz"
DOCKER_IMAGE="${DOCKER_IMAGE_NAME}:${VERSION}_${DOCKER_ENV}"

# Try to recover image from cache
if [ -f $IMAGE_BACKUP_FILE ]; then
    echo "Recovering ${DOCKER_IMAGE} from backup"
    docker load -i $IMAGE_BACKUP_FILE
    exit 0 
fi

echo "Building new docker image for ${DOCKER_ENV} version ${VERSION}"

# If we can't recover an existing image, create and persist to cache
scripts/build_image.sh ${DOCKER_ENV}
BUILD_ERROR_CODE=$?

# Check if the build succeeded
if [[ $BUILD_ERROR_CODE != 0 ]]; then
    echo "Could not build the ${DOCKER_ENV} image (error code: ${BUILD_ERROR_CODE})"
    exit 2
fi

# Remove old images and backup the newly created one
echo "Saving the docker image to the cache folder"
mkdir -p "docker/cache"
rm -rf "docker/cache/${DOCKER_ENV}_*"
docker save -o ${IMAGE_BACKUP_FILE} $DOCKER_IMAGE
exit $?
