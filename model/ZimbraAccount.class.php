<?php

/**
 * A Zimbra mail account.
 *
 * @author LiberSoft <info@libersoft.it>
 * @license http://www.gnu.org/licenses/gpl.txt
 */
class ZimbraAccount extends lsZimbraObject
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
