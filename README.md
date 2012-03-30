CONTENTS OF THIS FILE
---------------------

 * About zcs-php
 * Support
 * Usage and Examples

ABOUT ZCS-PHP
---------------

zcs-php is a small set of PHP classes to query a Zimbra Collaboration Suite server via the SOAP API interface.

zcs-php classes are released under the terms of GPLv3, see LICENSE.txt

REQUIREMENTS
------------

- PSR-0 compatible autoloader
- PHP 5.3


SUPPORT
-------

You can file a bug report or file a feature request at:

https://github.com/libersoft/zcs-php/issues

We can provide commercial support and payed features, drop us a line at info@libersoft.it

USAGE AND EXAMPLES
------------------

The zsc-php library is a [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) compatible library which means you can use the autoloading powers of PHP out of the box.

All you need is a framework with a PSR-0 compatible autoloader or any other autoloader that is PSR-0 compatible. [The official one for example](https://gist.github.com/221634).

The main class to use is \Zimbra\ZCS\Admin. \Zimbra\ZCS\SoapClient is used to build and send XML SOAP messages. If something goes wrong, a \Zimbra\ZCS\Exception is raised, containing an error message.

Here follows an excerpt of a simple \Zimbra\ZCS\Admin usage:

    <?php
    // Making sure the Zimbra library can be found
    require_once 'autoloader.php'; // The PSR-0 autoloader from https://gist.github.com/221634
    $classLoader = new SplClassLoader('Zimbra', realpath(__DIR__.'/zcs-php/src/')); // Point this to the src folder of the zcs-php repo
    $classLoader->register();

    // Define some constants we're going to use
    define('ZIMBRA_LOGIN', 'foo');
    define('ZIMBRA_PASS',  'bar');
    define('ZIMBRA_SERVER', 'zcs.example.com');
    define('ZIMBRA_PORT', '7071');

    // Create a new Admin class and authenticate
    $zimbra = new \Zimbra\ZCS\Admin(ZIMBRA_SERVER, ZIMBRA_PORT);
    $zimbra->auth(ZIMBRA_LOGIN, ZIMBRA_PASS);

    // Get all available accounts from a domain
    $accounts = $zimbra->getAccounts(array(
        'domain' => 'www.example.com',
        'offset' => 0,
        'limit'  => 100
    ));

    // And output them
    foreach ($accounts as $account){
        echo $account->name . '<br/>';
    }