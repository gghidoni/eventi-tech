#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPORT_DIR="${ROOT_DIR}/storage/testing/security"
REPORT_FILE="${REPORT_DIR}/semgrep.json"
SEMGREP_IMAGE="${SEMGREP_IMAGE:-semgrep/semgrep:1.163.0}"

mkdir -p "${REPORT_DIR}"

docker run --rm \
  --user "$(id -u):$(id -g)" \
  --env HOME=/tmp/semgrep \
  --volume "${ROOT_DIR}:/src" \
  --workdir /src \
  "${SEMGREP_IMAGE}" \
  semgrep scan \
  --config p/php \
  --config p/secrets \
  --json \
  --output /src/storage/testing/security/semgrep.json \
  --error

printf 'Semgrep report written to %s\n' "${REPORT_FILE}"
