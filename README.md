Networkteam.SentryClient
========================

This is a Sentry client package for the Flow framework and Neos CMS (https://www.neos.io).

It's based on https://github.com/getsentry/sentry-php.

Have a look at https://sentry.io for more information about Sentry.

Installation:
-------------

    $ composer require networkteam/sentryclient

_Compatibility table for the sentry client_

There was a failure in the versioning scheme for the client from 1.0.3 on. This is fixed with the 2.x and 3.x versions.

|    Flow         |SentryClient |
|-----------------|-------------|
|>= 5.0.0         | 5.x         |
|>= 4.0.0 < 5.0   | 4.x         |
|>=2.3.9, >=3.0.3 | 3.x, >1.0.3 |
|<2.3.9, <3.0.3   | 2.x, <1.0.3 |

Configuration:
--------------

Add the following to your `Settings.yaml` and replace the `dsn` setting with your project DSN (API Keys in your Sentry project):

    Networkteam:
      SentryClient:
        # The Sentry DSN
        dsn: 'http://public_key:secret_key@your-sentry-server.com/project-id'

You can implement the `\Networkteam\SentryClient\User\UserContextServiceInterface` to pass your own user context 
information to the logging. If you do not have the TYPO3.Party Package and don't want to implement your own 
`UserContextService` you need to set the `\Networkteam\SentryClient\User\DummyUserContext` in the Objects.yaml like

    Networkteam\SentryClient\User\UserContextServiceInterface:
      className: Networkteam\SentryClient\User\DummyUserContext

This will prevent any collection of user information except information that is available via the Flow SecurityContext.

Usage:
------

Sentry will log all exceptions that have the rendering option `logException` enabled. This can be enabled or disabled
by status code or exception class according to the Flow configuration.

Development:
------------

This package is managed on GitHub. Feel free to get in touch at https://github.com/networkteam/Networkteam.SentryClient.

License:
--------

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).
