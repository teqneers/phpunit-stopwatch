#!/bin/sh
#
# Resolves dependencies for THIS container's PHP version, then runs the given
# command (defaults to the test suite).
#
# `composer update` rewrites composer.json/composer.lock, so both are snapshotted
# and restored: the mounted host repository is left untouched and vendor/ lives in
# an isolated per-version Docker volume rather than on the host.
#
# By default the library's internal static-analysis tooling (psalm, rector,
# php-cs-fixer, ...) is dropped before resolving: it is not needed to RUN the
# suite and it drags in PHP-version ceilings (e.g. vimeo/psalm ^5 forbids PHP > 8.3)
# that would otherwise make the suite unrunnable on newer PHP. Set KEEP_DEV_TOOLS=1
# to resolve the full dev dependency set (used by `make docker-analysis`).
set -eu

snapshot_dir="$(mktemp -d)"
[ -f composer.json ] && cp composer.json "$snapshot_dir/composer.json"
[ -f composer.lock ] && cp composer.lock "$snapshot_dir/composer.lock"

restore_manifests() {
    [ -f "$snapshot_dir/composer.json" ] && cp "$snapshot_dir/composer.json" composer.json
    [ -f "$snapshot_dir/composer.lock" ] && cp "$snapshot_dir/composer.lock" composer.lock
    rm -rf "$snapshot_dir"
}

# Always put the committed manifests back, even if composer fails or the run aborts.
trap restore_manifests EXIT INT TERM

if [ "${KEEP_DEV_TOOLS:-0}" != "1" ]; then
    composer remove --dev --no-update --no-interaction \
        vimeo/psalm psalm/plugin-phpunit rector/rector \
        ergebnis/php-cs-fixer-config ergebnis/composer-normalize \
        ergebnis/license >/dev/null 2>&1 || true
fi

echo "» PHP $(php -r 'echo PHP_VERSION;') — resolving dependencies${COMPOSER_FLAGS:+ ($COMPOSER_FLAGS)}..."
# shellcheck disable=SC2086
composer update --no-progress --no-interaction ${COMPOSER_FLAGS:-}

# Restore now: the exec below replaces this shell, so the EXIT trap would not run
# on the success path.
restore_manifests
trap - EXIT INT TERM

exec "$@"
