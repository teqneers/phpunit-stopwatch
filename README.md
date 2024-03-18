# phpunit-stopwatch

[![CI](https://github.com/teqneers/phpunit-stopwatch/actions/workflows/ci.yml/badge.svg)](https://github.com/teqneers/phpunit-stopwatch/actions)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teqneers/phpunit-stopwatch/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/teqneers/phpunit-stopwatch/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/teqneers/phpunit-stopwatch/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/teqneers/phpunit-stopwatch/?branch=main)
[![Code Climate](https://codeclimate.com/github/teqneers/phpunit-stopwatch/badges/gpa.svg)](https://codeclimate.com/github/teqneers/phpunit-stopwatch)

[![codecov](https://codecov.io/gh/teqneers/phpunit-stopwatch/graph/badge.svg?token=U1T7ZGW5XW)](https://codecov.io/gh/teqneers/phpunit-stopwatch)
[![Type Coverage](https://shepherd.dev/github/teqneers/phpunit-stopwatch/coverage.svg)](https://shepherd.dev/github/teqneers/phpunit-stopwatch)

This project provides a [`composer`](https://getcomposer.org) package and
a [Phar archive](https://www.php.net/manual/en/book.phar.php) with an extension for measuring and analysing parts of
your code during a test run with [`phpunit/phpunit`](https://github.com/sebastianbergmann/phpunit).

The extension is compatible with the following versions of `phpunit/phpunit`:

- [`phpunit/phpunit:^10.1.0`](https://github.com/sebastianbergmann/phpunit/tree/10.1.0)
- [`phpunit/phpunit:^11.0.0`](https://github.com/sebastianbergmann/phpunit/tree/11.0.0)

Once you've added some measurement points to your code, the extension will stop watch and count them. The results are
displayed for each test and as a total report at the end of the test run.

Here is an example of how the output of a single test class might look:

```console
Stopwatch for TQ\Tests\Example\SingleTest::testDataCalculation:
- TQ\Testing\Database::deleteData                         0.117secs (    3x, Ø   0.04) TOTAL    327.026secs (  184x, Ø   1.78)
- ...onment\Testing::cleanupInstance                      0.259secs (    1x, Ø   0.26) TOTAL      6.159secs (   60x, Ø   0.10)
- TQ\Testing\Database::import                             7.889secs (   11x, Ø   0.72) TOTAL    250.958secs (  352x, Ø   0.71)
- Test                                                    1.428secs (    1x, Ø   1.43) TOTAL   1041.228secs (   70x, Ø  14.87)
.

Stopwatch for TQ\Tests\Example\SingleTest::testDataTransfer:
- TQ\Testing\Database::deleteData                         0.116secs (    3x, Ø   0.04) TOTAL    327.142secs (  187x, Ø   1.75)
- ...onment\Testing::cleanupInstance                      0.256secs (    1x, Ø   0.26) TOTAL      6.415secs (   61x, Ø   0.11)
- TQ\Testing\Database::import                             7.573secs (   11x, Ø   0.69) TOTAL    258.531secs (  363x, Ø   0.71)
- Test                                                    5.998secs (    1x, Ø   6.00) TOTAL   1047.226secs (   71x, Ø  14.75)
.

Stopwatch for TQ\Tests\Example\SingleTest TearDown:
- TQ\Testing\Database::deleteData                        38.486secs (    6x, Ø   6.41) TOTAL    365.511secs (  190x, Ø   1.92)
- ...onment\Testing::cleanupInstance                      0.256secs (    1x, Ø   0.26) TOTAL      6.415secs (   61x, Ø   0.11)
- TQ\Testing\Database::import                             7.573secs (   11x, Ø   0.69) TOTAL    258.531secs (  363x, Ø   0.71)
- Test                                                    5.998secs (    1x, Ø   6.00) TOTAL   1047.226secs (   71x, Ø  14.75)
```

And at the end of the test run, you will get a summary of all stopwatches used, and it is going to look like this:

```console
Stopwatch TOTALS:
- Test                                                                                 TOTAL   1047.246secs (   78x, Ø  13.43)
- TQ\Testing\Database::deleteData                                                      TOTAL    365.511secs (  190x, Ø   1.92)
- TQ\Testing\Database::import                                                          TOTAL    258.531secs (  363x, Ø   0.71)
- ...onment\Testing::cleanupInstance                                                   TOTAL      6.416secs (   62x, Ø   0.10)
- TQ\Production\Monitoring::ping                                                       TOTAL     17.967secs (    7x, Ø   2.57)
```

### Usage

Stopwatch is very easy to use. All you need is to start and stop the stopwatch anywhere in your code with a single line
of code.
Let's say your tests are pretty slow, but you don't know who's the culprit? Your guess is that it might be in the
database setup.
Go there and add this around the code you suspect:

```php
        Stopwatch::start(__METHOD__);

        self::initializeDatabase(
            $db->getConnection(),
            ...static::createData()
        );

        Stopwatch::stop(__METHOD__);
```

If a test, a setUp, tearDown, setUpBeforeClass or tearDownAfterClass method executes that code, the stopwatch will
measure the time it takes to execute the code between the start and stop calls and display it in the test output.

## Installation

### Installation with `composer`

Run

```sh
composer require --dev teqneers/phpunit-stopwatch
```

to install `teqneers/phpunit-stopwatch` as a `composer` package.

### Installation as Phar

Download `phpunit-stopwatch.phar` from
the [latest release](https://github.com/teqneers/phpunit-stopwatch/releases/latest).

## Usage

### Bootstrapping the extension

Before the extension can detect slow tests in `phpunit/phpunit`, you need to bootstrap it. The bootstrapping mechanism
depends on the version of `phpunit/phpunit` you are using.

### Bootstrapping the extension as a `composer` package

To bootstrap the extension as a `composer` package when using

- `phpunit/phpunit:^10.0.0`
- `phpunit/phpunit:^11.0.0`

adjust your `phpunit.xml` configuration file and configure the

- [`extensions` element](https://docs.phpunit.de/en/10.5/configuration.html#the-extensions-element)
  on [`phpunit/phpunit:^10.0.0`](https://docs.phpunit.de/en/10.5/)
- [`extensions` element](https://docs.phpunit.de/en/11.0/configuration.html#the-extensions-element)
  on [`phpunit/phpunit:^11.0.0`](https://docs.phpunit.de/en/11.0/)

```diff
 <phpunit
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
     bootstrap="vendor/autoload.php"
 >
+    <extensions>
+        <bootstrap class="TQ\Testing\Extension\Stopwatch\Extension"/>
+    </extensions>
     <testsuites>
         <testsuite name="unit">
             <directory>test/Unit/</directory>
         </testsuite>
     </testsuites>
 </phpunit>
```

### Bootstrapping the extension as a PHAR

To bootstrap the extension as a PHAR when using

- `phpunit/phpunit:^10.0.0`
- `phpunit/phpunit:^11.0.0`

adjust your `phpunit.xml` configuration file and configure the

- [`extensionsDirectory` attribute](https://docs.phpunit.de/en/10.5/configuration.html#the-extensionsdirectory-attribute)
  and the [`extensions` element](https://docs.phpunit.de/en/10.5/configuration.html#the-extensions-element)
  on [`phpunit/phpunit:^10.0.0`](https://docs.phpunit.de/en/10.5/)
- [`extensionsDirectory` attribute](https://docs.phpunit.de/en/11.0/configuration.html#the-extensionsdirectory-attribute)
  and the [`extensions` element](https://docs.phpunit.de/en/11.0/configuration.html#the-extensions-element)
  on [`phpunit/phpunit:^11.0.0`](https://docs.phpunit.de/en/11.0/)

```diff
 <phpunit
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
     bootstrap="vendor/autoload.php"
+    extensionsDirectory="directory/where/you/saved/the/extension/phars"
 >
+    <extensions>
+        <extension class="TQ\Testing\Extension\Stopwatch\Extension"/>
+    </extensions>
     <testsuites>
         <testsuite name="unit">
             <directory>test/Unit/</directory>
         </testsuite>
     </testsuites>
 </phpunit>
```

### Configuring the extension

So far, there are no configuration settings for the extension.

### Running tests

When you have bootstrapped the extension, you can run your tests as usually. E.g.:

```sh
vendor/bin/phpunit
```

When the extension is used somewhere in your code, it will give you a report:

## License

This project uses the [MIT license](LICENSE.md).

## Credits

This package is inspired by [`ergebnis/phpunit-slow-test-detector`](https://github.com/ergebnis/phpunit-slow-test-detector/).
