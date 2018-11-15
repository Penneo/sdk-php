#!/bin/bash

# Make sure that the working directory is the Symfony root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../../"

# Update composer and install dependencies
composer selfupdate
# Prefer source is needed to get the guzzle test client
composer install --no-interaction --prefer-source
