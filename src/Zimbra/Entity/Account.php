<?php

/**
 * A Zimbra mail account.
 *
 * @author LiberSoft <info@libersoft.it>
 * @author Chris Ramakers <chris.ramakers@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.txt
 */

namespace Zimbra\Entity;

class Account extends \Zimbra\Entity
{

    protected $status = array(
        'active' => 'Attivo',
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
        return $this->status[$this->get('zimbraAccountStatus')];
    }

}
