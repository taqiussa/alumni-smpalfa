#!/usr/bin/env bash
set -euo pipefail

IMAGE="taqiussa/smpalfa:alumni"
SERVICE="smpalfa_alumni"
: "${DEPLOY_SERVER:?Set DEPLOY_SERVER to the Docker Swarm manager SSH host.}"

echo "1. Build image"
docker build --tag "$IMAGE" .

echo "2. Push image"
docker push "$IMAGE"

echo "3. Wait for registry propagation"
sleep 10

echo "4. Update Swarm service"
ssh "$DEPLOY_SERVER" bash -s -- "$IMAGE" "$SERVICE" <<'REMOTE'
set -euo pipefail

IMAGE="$1"
SERVICE="$2"

docker service inspect "$SERVICE" >/dev/null
docker pull "$IMAGE"

docker service update \
  --image "$IMAGE" \
  --no-resolve-image \
  --force \
  --update-parallelism 1 \
  --update-delay 10s \
  --update-order start-first \
  --update-failure-action rollback \
  --update-monitor 10s \
  --detach=false \
  "$SERVICE"

SERVICE_IMAGE="$(docker service inspect --format '{{.Spec.TaskTemplate.ContainerSpec.Image}}' "$SERVICE")"
if [[ "$SERVICE_IMAGE" != "$IMAGE" ]]; then
  echo "ERROR: $SERVICE image is $SERVICE_IMAGE, expected $IMAGE" >&2
  exit 1
fi

docker service ps --no-trunc "$SERVICE"
REMOTE

echo "Deployment complete: $SERVICE"
