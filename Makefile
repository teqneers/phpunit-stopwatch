.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: it
it: refactoring coding-standards security-analysis static-code-analysis tests ## Runs the refactoring, coding-standards, security-analysis, static-code-analysis, and tests targets

vendor: composer.json composer.lock
	composer validate --strict
	composer install --no-interaction --no-progress

.PHONY: static-code-analysis
static-code-analysis: vendor ## Runs a static code analysis with vimeo/psalm
	vendor/bin/psalm --config=psalm.xml --clear-cache
	vendor/bin/psalm --config=psalm.xml --show-info=false --stats --threads=4 --shepherd

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: vendor ## Generates a baseline for static code analysis with vimeo/psalm
	vendor/bin/psalm --config=psalm.xml --clear-cache
	vendor/bin/psalm --config=psalm.xml --set-baseline=psalm-baseline.xml

.PHONY: security-analysis
security-analysis: vendor ## Runs a security analysis with composer
	composer audit

.PHONY: refactoring
refactoring: vendor ## Runs automated refactoring with rector/rector
	vendor/bin/rector process --config=rector.php

.PHONY: tests
tests: phar ## Runs unit, end-to-end, and phar tests with phpunit/phpunit
	vendor/bin/phpunit --testdox --display-warnings --display-notices
	#vendor/bin/phpunit --configuration=test/EndToEnd/Version11/phpunit.xml
    #vendor/bin/phpunit --configuration=test/Phar/Version10/phpunit.xml

.PHONY: phar
phar: phive ## Builds a phar with humbug/box
	.phive/box validate box.json
	#composer remove ergebnis/php-cs-fixer-config psalm/plugin-phpunit vimeo/psalm --dev --no-interaction --quiet
	#composer remove phpunit/phpunit --no-interaction --quiet
	.phive/box compile --config=box.json
	#git checkout HEAD -- composer.json composer.lock
	.phive/box info .build/phar/phpunit-stopwatch.phar --list

.PHONY: phive
phive: .phive ## Installs dependencies with phive
	PHIVE_HOME=.build/phive phive install --trust-gpg-keys 0x2DF45277AEF09A2F,0x033E5F8D801A2F8D,0x033E5F8D801A2F8D

.PHONY: code-coverage
code-coverage: vendor ## Collects coverage from running unit tests with phpunit/phpunit
	vendor/bin/phpunit --coverage-text

.PHONY: dependency-analysis
dependency-analysis: phive vendor ## Runs a dependency analysis with maglnet/composer-require-checker
	.phive/composer-require-checker check --config-file=$(shell pwd)/composer-require-checker.json --verbose

.PHONY: coding-standards
coding-standards: vendor ## Lints YAML files with yamllint, normalizes composer.json with ergebnis/composer-normalize, and fixes code style issues with friendsofphp/php-cs-fixer
	yamllint -c .yamllint.yaml --strict .
	composer normalize
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --diff --show-progress=dots --verbose
