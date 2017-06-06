# Introduction

Very simple Cookie and PHP Session library.

# Why

* PHP >= 5.4 support for CentOS 7;
* Replace complicated `setcookie()` which is not secure by default (`HttpOnly`, 
  `Secure`, `SameSite` are not the default);
* [delight-im/cookie](https://github.com/delight-im/PHP-Cookie) and 
  [paragonie/cookie](https://github.com/paragonie/PHP-Cookie), in addition to 
  requiring PHP >= 5.6, parse cookies, which is a liability;
* Allow binding PHP Sessions to "Domain" and "Path" (see below);
* Easy to use PHP Session API;
* Uses a "Canary" to regularly refresh session ID;
* Implements `SameSite`;
* Unit tests with PHPUnit;

# Cookies

Setting a cookie, i.e. add the `Set-Cookie` header is straightforward:

    $cookie = new Cookie();
    $cookie->set('foo', 'bar');

This will set the cookie using the `Secure`, `HttpOnly` and `SameSite=Strict` 
values. 

The following configuration options are supported:

* `Secure`: `bool` (default: `true`)
* `HttpOnly`: `bool` (default: `true`)
* `Path`: `string`|`null` (default: `null`)
* `Domain`: `string`|`null` (default: `null`)
* `Max-Age`: `int`|`null` (default: `null`)
* `SameSite`: `Strict`|`Lax`|`null` (default: `Strict`)

For example, to limit a browser to only send the cookie to the `/foo/` path and
use the `Lax` option for `SameSite`:

    $cookie = new Cookie(
        [
            'Path' => '/foo/',
            'SameSite' => 'Lax',
        ]
    );
    $cookie->set('foo', 'bar');

You can delete a cookie, i.e. set the value to `""` by using the `delete()` 
method:

    $cookie->delete('foo');

# Sessions

Sessions will use the same defaults as Cookies, so you'll get secure sessions
out of the box. 

    $session = new Session();
    $session->set('foo', 'bar');

Note that the values here are stored _inside_ the session, and not sent to the
browser!

You can destroy a session, i.e. empty the `$_SESSION` variable and regenerate 
the session ID by calling the `destroy()` method:

    $session->destroy();

There are methods for `get()`, `set()`, `has()` and `delete()` as well.

## Session Binding

Session binding is implemented to avoid using a PHP Session meant for one 
"Domain" or "Path" being used at another Domain or Path. This is important if 
you are hosting a "multi-site" application where the site is running at 
multiple domains, but shares the same PHP session storage.

This can be used like this:

    $session = new Session(
        [
            'DomainBinding' => 'www.example.org',
            'PathBinding' => '/foo/',
        ]
    );

This does *not* restrict the `Domain` and `Path` options for the Cookie, to 
modify these you'd have to also specify the `Domain` and `Path` options, but
leaving them empty can result in more secure cookies as they will be 
automatically bound to the "Path" and "Domain" that set them, see 
_The definitive guide to cookie domains and why a www-prefix makes your website safer_
linked in the resources below.

# Security

It is **very** important that you update your PHP Session settings in 
`php.ini` on your host. See _The Fast Track to Safe and Secure PHP Sessions_, 
linked below in the resources.
 
# Resources

* [The Fast Track to Safe and Secure PHP Sessions](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions)
* [The definitive guide to cookie domains and why a www-prefix makes your website safer](http://erik.io/blog/2014/03/04/definitive-guide-to-cookie-domains/)
