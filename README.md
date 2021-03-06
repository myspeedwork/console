# Console Service Provider
=============================================
[![codecov](https://codecov.io/gh/speedwork/console/branch/master/graph/badge.svg)](https://codecov.io/gh/speedwork/view)
[![StyleCI](https://styleci.io/repos/67978616/shield)](https://styleci.io/repos/67978616)
[![Latest Stable Version](https://poser.pugx.org/speedwork/console/v/stable)](https://packagist.org/packages/speedwork/console)
[![Latest Unstable Version](https://poser.pugx.org/speedwork/console/v/unstable)](https://packagist.org/packages/speedwork/console)
[![License](https://poser.pugx.org/speedwork/console/license)](https://packagist.org/packages/speedwork/console)
[![Total Downloads](https://poser.pugx.org/speedwork/console/downloads)](https://packagist.org/packages/speedwork/console)
[![Build status](https://ci.appveyor.com/api/projects/status/10aw52t4ga4kek27?svg=true)](https://ci.appveyor.com/project/2stech/console)
[![Build Status](https://travis-ci.org/speedwork/console.svg?branch=master)](https://travis-ci.org/speedwork/console)

## Introduction

Speed is the name of the command-line interface included with Speedwork. It provides a number of helpful commands for your use while developing your application. It is driven by the powerful Symfony Console component. To view a list of all available Speedwork commands, you may use the `list` command:

    php ./console list

Every command also includes a "help" screen which displays and describes the command's available arguments and options. To view a help screen, simply precede the name of the command with `help`:

    php ./console help serve

#Contributing
=============================================

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Run the tests, adding new ones for your own code if necessary (`phpunit`)
5. Commit your changes (`git commit -am 'Added some feature'`)
6. Push to the branch (`git push origin my-new-feature`)
7. Create new Pull Request
