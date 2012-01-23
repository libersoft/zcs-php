CONTENTS OF THIS FILE
---------------------

 * About zcs-php
 * Support
 * Usage and Examples

ABOUT ZCS-PHP
---------------

zcs-php is a small set of PHP classes to query a Zimbra Collaboration Suite server via the SOAP API interface.

zcs-php classes are released under the terms of GPLv3, see LICENSE.txt

SUPPORT
-------

You can file a bug report or file a feature request at:

https://github.com/libersoft/zcs-php/issues

We can provide commercial support and payed features, drop us a line at info@libersoft.it

USAGE AND EXAMPLES
------------------

The main class to use is ZimbraAdmin. ZimbraSOAP is used to build and send XML SOAP messages.
If something go wrong, ZimbraException is raised, containing an error message.

Here follows an excerpt of a simple ZimbraAdmin usage:

<?php
require_once(dirname(__FILE__) . '/model/lsZimbraObject.class.php');
require_once(dirname(__FILE__) . '/model/ZimbraAccount.class.php');
require_once(dirname(__FILE__) . '/model/ZimbraDistributionList.class.php');
require_once(dirname(__FILE__) . '/model/ZimbraServer.class.php');
require_once "ZimbraAdmin.php";
require_once "ZimbraSOAP.php";
require_once "ZimbraException.php";

$zimbraadminemail = 'admin@zimbra.domain.com';
$zimbraadminpassword = 'adminpassword';
$zimbraadmindomain = 'domainToAdminister';

$zimbra = new ZimbraAdmin('zimbra.domain.com', '7071');
$zimbra->auth($zimbraadminemail, $zimbraadminpassword);

// createAccount()
$newAccount = $zimbra->createAccount(array(
    'name' => 'test@'.$zimbraadmindomain,
    'password' => 'thepassword',
    'zimbraMailQuota' => '1024',
    'displayName' => 'Test',
));

// getAccount() pass
$account = $zimbra->getAccount($zimbraadmindomain, 'id', $newAccount->id);

// modifyAccount()
$account = $zimbra->modifyAccount(array(
    'id' => $newAccount->id,
    'zimbraMailQuota' => '2048',
));

// addAccountAlias() e removeAccountAlias()
$alias = 'test_alias@'.$zimbraadmindomain;
$success = $zimbra->addAccountAlias($newAccount->id, $alias);

$success = $zimbra->removeAccountAlias($newAccount->id, $alias);

// deleteAccount()
$deleted = $zimbra->deleteAccount($newAccount->id);

// getAccount() fail
try {
    $zimbra->getAccount($zimbraadmindomain, 'name', 'non-existing-account@non.existing.domain');
} catch (Exception $exc) {
    echo 'non-existing account';
}

// getTotalQuota()
$domainQuota = $zimbra->getTotalQuota($zimbraadmindomain);

Have Fun!
