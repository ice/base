#!/usr/bin/env bash
cd /opt/docker/provision/entrypoint.d/
for f in *.sh; do
  bash "$f" -H   || break # if needed
done

exec "$@"