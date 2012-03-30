<?php

/**
 * A Zimbra mail account.
 *
 * @author LiberSoft <info@libersoft.it>
 * @author Chris Ramakers <chris.ramakers@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.txt
 */

namespace Zimbra\ZCS\Entity;

class Account extends \Zimbra\ZCS\Entity
{

    static $statuses = array(
        'active' => 'Attivo',
        'closed' => 'Disattivo',
    );

    public function setAliases($aliases)
    {
        $this->set('aliases', implode("\n", $aliases));
    }

    public function getAliases()
    {
        return str_replace("\n", ', ', $this->aliases);
    }

    public function getStatus()
    {
        return self::$statuses[$this->get('zimbraAccountStatus')];
    }

    public static function getStatuses()
    {
        return self::$statuses;
    }

}
