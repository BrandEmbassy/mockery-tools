Verbose Mockery
===============

It is an extension to `\Mockery` class. It provides "verbose" output about mocked methods' parameters match instead of "no expectations found" unhelpful and vague message. 

How to use it
-------------

It is very simple - just replace regular `\Mockery` class with `\BrandEmbassy\MockeryTools\MockeryVerbose\MockeryVerbose` class in any test and the rest is magic.

Before:

```php
\Mockery::mock(ThisAndThat::class);
```

After:

```php
\BrandEmbassy\MockeryTools\MockeryVerbose\MockeryVerbose::mock(ThisAndThat::class);
```

Now just run the test and check the console output. 

**Note:** It is recommended to use MockeryVerbose for the local development only, as it brings another unnecessary overhead, useless for Jenkins runs. So, do not push it to remote branches.
