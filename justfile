set dotenv-load := true

set shell := ["bash", "-c"]

all:
    just grumphp
    just test "7.4"
    just test "8.0"
    just test "8.1"

_docker version command:
    docker run --rm -v $(pwd):/app -w /app kanti/buildy:{{ version }} {{ command }}

_clean:
    just _docker "8.0" "rm -rf .Build/ composer.lock"

install version="8.0":
    just _clean
    just _docker {{ version }} "composer install"

require req version="8.0":
    just _clean
    just _docker {{ version }} "composer req {{ req }}"

grumphp version="8.0":
    just install {{ version }}
    just _docker {{ version }} ".Build/bin/grumphp run"

phpstan version="8.0":
    just install {{ version }}
    just _docker {{ version }} "composer phpstan"

fix:
    just install "8.0"
    just _docker "8.0" "/app/.Build/bin/php-cs-fixer --config=.php-cs-fixer.dist.php --using-cache=no --verbose fix"

test version="8.0":
    just install {{ version }}
    just _docker {{ version }} "composer test"
