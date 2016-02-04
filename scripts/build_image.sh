#!/bin/bash

# Stuff needed for pretty printing
bold=$(tput bold)
normal=$(tput sgr0)

# Make sure that the working directory is the app root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../"

if [[ "$#" -lt 1 ]]; then
    # Find available environments
    ENVS=( $(find docker/* -name packer.json -print0 | xargs -0 -n1 dirname | sort --unique | cut -sd / -f 2-) )
    echo "Usage: build_image.sh [target]"
    echo "Available targets: ${bold}$(printf "%s " "${ENVS[@]}")${normal}"
    exit 1
fi

# Check that the Docker dotenv file is present.
if [ ! -f "docker/.env" ]; then
    echo ".env file is missing in the docker directory."
    exit 2
fi

DOCKER_ENV=$1
PACKER_FILE=docker/$DOCKER_ENV/packer.json

source docker/.env

# Check that the packer file is present
if [ ! -f "$PACKER_FILE" ]; then
    echo "Could not find $PACKER_FILE"
    exit 3
fi

# Create a tmp dir for use with packer
TMP="tmp"
mkdir ./$TMP;

# Create the docker image
TMPDIR=$(pwd)/$TMP packer build $PACKER_FILE
BUILD_ERROR_CODE=$?

rm -rf ./$TMP;

exit $BUILD_ERROR_CODE
