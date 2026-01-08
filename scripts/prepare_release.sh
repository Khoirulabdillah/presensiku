#!/usr/bin/env bash
set -e

echo "Preparing release for cPanel: ensuring vendor and public/build are present"

# Install composer deps locally if vendor missing
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
  if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --prefer-dist --optimize-autoloader
  else
    echo "composer not found: please run composer install locally before pushing to cPanel"
  fi
else
  echo "vendor/ exists, skipping composer install"
fi

# Build frontend if public/build missing
if [ ! -d "public/build" ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
  if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
  else
    echo "npm not found: please build assets locally (npm ci && npm run build) before pushing to cPanel"
  fi
else
  echo "public/build exists, skipping npm build"
fi

echo "Release prepared. You can now commit vendor/ and public/build to your repo for cPanel deployment."
