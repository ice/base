#!/usr/bin/env bash

# trace ERR through pipes
set -o pipefail

# trace ERR through 'time command' and other functions
set -o errtrace

# set -u : exit the script if you try to use an uninitialised variable
set -o nounset

# set -e : exit the script if any statement returns a non-true return value
set -o errexit

WWW_USER=${WWW_USER:-}
WWW_GROUP=${WWW_GROUP:-}
WWW_DOCUMENT_ROOT=${WWW_DOCUMENT_ROOT:-}

chown $WWW_GROUP:$WWW_USER $WWW_DOCUMENT_ROOT -R