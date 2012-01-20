<?php

/**
 * A Zimbra mail account.
 *
 * @author Matteo Giordano <mg@libersoft.it>
 */
class Account extends lsZimbraObject
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
