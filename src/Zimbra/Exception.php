<?php

/**
 * @author LiberSoft <info@libersoft.it>
 * @author Chris Ramakers <chris.ramakers@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.txt
 */
namespace Zimbra;

class Exception extends \Exception
{

    public function __construct($code)
    {
        parent::__construct(self::getErrorMessage($code));
    }

    private static function getErrorMessage($code)
    {
        switch ($code) {
            case 'account.ACCOUNT_EXISTS':
                return "account already exists";
                break;
            case 'account.DISTRIBUTION_LIST_EXISTS':
                return "distribution list already exists";
                break;
            case 'service.PROXY_ERROR':
                return "error while proxying request to target server";
                break;
            default:
                return sprintf("An unexpected error has occurred (%s)", $code);
        }
    }

}
