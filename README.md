# FFMVC PHP7

![Travis CI](https://travis-ci.org/vijinho/FFMVC.svg?branch=dev-master)

Files used by the https://github.com/vijinho/f3-boilerplate project.

This small library of classes can be used to start your own project based on a clone of the code in f3-boilerplate.

## Files

- `src/App.php` - Loads the configuration file and sets the environment based on project layout of f3-boilerplate.  Also contains the shutdown function to log the memory used and database queries.
- `src/Exceptions.php` - Base exceptions

## src/Helpers

- `DB.php` - Methods for parsing a http-style DSN and/or db params from a config array (or file) and return a connection to the database.
- `Notifications.php` - A general class to store/retrieve user notification messages in an array to the f3 hive.
- `Response.php` - A class to return a JSON-encoded HTTP message.
- `Str.php` - Some general string utility functions.
- `Time.php` - Date/time utility methods
- `Url.php` - General helper methods to create internal/external links.
- `Validator.php` - An extension to [GUMP](https://github.com/Wixel/GUMP) using some f3 string methods.
- `Mail.php` - A wrapper to return a pre-configured instance of [PHPMailer](https://github.com/PHPMailer/PHPMailer) based on f3 settings or pre-configured f3 built-in SMTP object 

Vijay Mahrra
http://about.me/vijay.mahrra
----
