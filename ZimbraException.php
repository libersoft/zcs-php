<?php

/**
 * @author LiberSoft <info@libersoft.it>
 * @license http://www.gnu.org/licenses/gpl.txt
 */

class ZimbraException extends Exception
{

    public function __construct($code)
    {
        parent::__construct(self::getErrorMessage($code));
    }

    private static function getErrorMessage($code)
    {
        switch ($code) {
            case 'account.ACCOUNT_EXISTS':
                return "Questo account o alias esiste già.";
                break;
            case 'account.DISTRIBUTION_LIST_EXISTS':
                return "L'elenco di distribuzione esiste già.";
                break;
            default:
                return sprintf("An unexpected error has occurred (%s)", $code);
        }
    }

}
