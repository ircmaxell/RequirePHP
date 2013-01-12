RequirePHP
==========

## What Is RequirePHP

Basically, RequirePHP is an [AMD](https://github.com/amdjs/amdjs-api/wiki/AMD) inspired dependency injector and loader for PHP.

It is a HIGHLY experimental and unstable product at this point.

## Ok, What Can It Do?

Right now, it can load and return dependencies, both object, and primitive. It does it asynchronously. Right now, no asynchronous loaders are defined (so everything is synchronous), but the system fully supports a Promise-based loader.

## Install

1. Checkout the master version of this repo.

2. Run composer install

## Use:

Check out test.php for more usage details.

## API:

* `define(id?, deps?, factory)` - Defines how to resolve a dependency. See below for full ID description, but if the module is `raw`, it will return the factory directly rather than processing it.

        $amd->define('foo', 1); // Sets the foo key to 1.
        $amd->define('time', 'time'); // Sets the key foo to the current time stamp as of the first time the dependency is used.
        $amd->define('bar', 'stdclass'); // Sets the key bar to a new StdClass object
        $amd->define('raw!bar', 'stdclass'); // Sets the key bar to the literal string stdclass

* `alias(id, id2)` - Says to use key `id2` for any request for `id`. This is useful for telling the loader how to resolve interfaces.

        $amd->alias('barInterface', 'bar'); // Requests for barInterface will be resolved the same as for bar.

* `with(dependencies, callback)` - Load dependencies, and call callback when they are all loaded.

        $amd->with(array('foo', 'bar'), function($foo, $bar) {
            echo $foo; // 1
            var_dump($bar); // string(8) "stdclass"
        });

## ID:

The default value of an ID is to be treated like a class name. So a request for `FooBar` would try to load the class.

However, IDs can be prefixed by module names for varying behavior. Currently supported prefixes are:

* `text!` - Searches the configured paths for a filename matching the ID (following !), and returns the contents as a string

* `new!` - Prevents caching of the instance, and always re-instantiates it for each request (calls the factory function or constructor each time).

* `clone!` - Clones the object for each call

* `twig!` - Treats the ID as a twig template name, and returns a callback that proxies to the appropriate twig render function.

        $amd->with(array('twig!test.twig'), function($template) {
            echo $template(array('foo' => 'bar'));
        });

## Special IDs:

These IDs are always defined by the system, and will return special functionality:

* `require` - Returns a function wrapping `$amd->with()`
* `with` - Returns a function wrapping `$amd->with()`
* `define` - Returns a function wrapping `$amd->define()`
* `amd` - The current AMD instance
* `when` - The Promise WHEN function.

## Promise

The package also includes a promise based deferred system. If you want to know more, it pretty closely mirrors the jQuery deferred object.

## Why?

For fun. It was a concept, and is a learning tool. If you find this interesting, consider playing with it and letting me know what you think...