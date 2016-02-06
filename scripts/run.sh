#!/bin/bash

# Make sure that the working directory is the app root dir
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}/../"

if [[ "$#" -lt 1 ]]; then
	echo -e "Usage: run.sh [environment] [setup (optional)]\n"
	echo "Available environments: "
	OUTPUT="ENVIRONMENT\tSETUP\tCOMMAND\n"

	# Find executable environments
	IFS=$'\n'
	ENVS=( $(find docker/* -name docker-compose*.yml | sed -e 's/docker\/\([^\/]*\)\/docker-compose-*\([^\.]*\)\.yml/\1 \2/g') )
	for i in "${ENVS[@]}"
	do
		IFS=$' '
		data=( $i )
		OUTPUT="$OUTPUT\n${data[0]}\t${data[1]:--}\trun.sh $i\n"
	done
	echo -ne $OUTPUT | column -ts $'\t'
	exit 1
fi

if [ ! -f "docker/.env" ]; then
	echo ".env file is missing in the docker directory."
	exit 2
fi

source docker/.env

DOCKER_ENV=$1
COMPOSE_FILE="docker/$DOCKER_ENV/docker-compose.yml"

if [[ "$#" -gt 1 ]]; then
	# Determine if an alternative compose file is requested.
	if [ -f "docker/$DOCKER_ENV/docker-compose-$2.yml" ]; then
		COMPOSE_FILE="docker/$DOCKER_ENV/docker-compose-$2.yml"
		CMD=${@:3}
	else
		# Interpret the second parameter and on as a command for docker-compose
		CMD=${@:2}
	fi
fi

# Check if the docker-compose file exist.
if [ ! -f "$COMPOSE_FILE" ]; then
	echo "Usage: run.sh [env]"
	echo "Could not find $COMPOSE_FILE"
	exit 3
fi

if [[ "$CMD" ]]; then
	# Run $CMD in the container
	eval docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME $CMD
else
	# Spin up the container in detached mode
	docker-compose -f $COMPOSE_FILE -p $PROJECT_NAME up -d
fi
