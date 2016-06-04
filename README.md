# php-mmap
This library is a lightweight implementation of c's mmap. In previous versions of PHP, `fopen` used mmap where possible, but it looks like 
this was dropped in 5.3.  The actual mapping is delegated to a python subprocess for compatibility and compilability .

This started as a component of another project, but it made more sense for it to be stand alone.  It is synchronous in operation, but if 
you're reading large amounts of data it can be done piece by piece.

I have tried to make it functionally equivalent to c's implementation, but it's not quite complete due to some limitations.

It would be nice for this to develop into a native php extension that can be phpize'd as a post install if the environment supports it.

## Setup

Using composer:

```json
  "require": {
  	"calcinai/php-mmap": "^0.1"
  }
```

## Usage

Via a URI (of sorts)
```php
    $mmap = fopen('mmap:///dev/mem:1024?offset=0', 'rw');
```

Via wrapper method
```php
    $mmap = mmap_open('/dev/mem', 1024, 0);
```

You can use `fseek()`, `fread()` and `fwrite()` on the stream.  If you have a use for others, let me know.