#!/usr/bin/env bash
set -euo pipefail
# build.sh — Encode with ionCube (if available) or create/update runtime payload.
# Requirements for real ionCube build:
#   - ionCube Encoder CLI installed, e.g. `ioncube_encoder10` for PHP 8.x
#   - ionCube Loader installed on target server
# Usage:
#   ./build.sh                # auto-detect encoder & build dist/app_encoded.php
#   ./build.sh runtime        # only refresh runtime obfuscated build (no ionCube)

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC="$ROOT/app_plain.php"
DIST="$ROOT/dist/app_encoded.php"
RT="$ROOT/runtime/app_runtime.php"

if [[ "${1:-}" == "runtime" ]]; then
  echo "[runtime] nothing to do (already shipped in runtime/app_runtime.php)"
  exit 0
fi

# Try to find an ionCube encoder binary
ENCODER="${IONCUBE_ENCODER:-}"
if [[ -z "$ENCODER" ]]; then
  for c in ioncube_encoder10 ioncube_encoder9 ioncube_encoder8; do
    if command -v "$c" >/dev/null 2>&1; then ENCODER="$c"; break; fi
  done
fi

if [[ -z "$ENCODER" ]]; then
  echo "(!) ionCube encoder not found. Falling back to runtime mode."
  echo "    -> Use: ./build.sh runtime"
  exit 0
fi

mkdir -p "$ROOT/dist"

echo "[ioncube] using encoder: $ENCODER"
# Example encode command (no expiry). Adjust switches as needed.
# Common flags: -o output -s strip_comments -b binary -keep -php 80-82 -AllowedServer 'domain.com'
"$ENCODER" "$SRC" -o "$DIST" -s -keep
echo "[ioncube] built: $DIST"
