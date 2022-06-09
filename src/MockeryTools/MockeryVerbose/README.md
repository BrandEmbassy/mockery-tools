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

**Note:** It is a good idea not to push the MockeryVerbose to production. Instead, it should be used only during local development. Otherwise, it just adds overhead and doesn't really bring anything to the table once on the production.
