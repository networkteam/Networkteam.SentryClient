Networkteam.SentryClient
========================

This is a Sentry client package for TYPO3 Flow and Neos.

It's based on https://github.com/getsentry/raven-php

Have a look at https://getsentry.com for more information about Sentry

Installation:
-------------

    $ composer require networkteam/sentryclient

Configuration:
--------------

Add the following to your Settings.yaml and replace the DSN with your projects DSN

    Networkteam:
      SentryClient:
        # The Sentry DSN
        dsn: 'http://public_key:secret_key@your-sentry-server.com/project-id'

Development:
------------

This package is managed on GitHub. Feel free to get in touch at https://github.com/networkteam/Networkteam.SentryClient