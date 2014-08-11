IfpaApi
=======

A PHP wrapper class to simplify access the IFPA API.

Installation
============

The easiest way to install the library is using Composer. Add the following to your project's composer.json, under the "require" key:

		"heyrocker/ifpaapi": "dev-master"

and run

		php composer.phar update

and everything will get installed.

If you're confused by this new-fangled stuff you can always just drop the PHP file somewhere and include it.

Usage
=====

In order to use the class you will need your API key. If you don't have an API key you can get one by visiting http://www.ifpapinball.com/api.

		$ifpa = new IfpaApi("<your_key");

Once you have an IfpaApi object, there are a variety of methods you can call which correspond to API calls and return appropriate objects.

Have fun!

