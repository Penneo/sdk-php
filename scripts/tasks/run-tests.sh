#!/bin/bash

# Make sure that the working directory is the Symfony root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../../"

bin/behat -f progress