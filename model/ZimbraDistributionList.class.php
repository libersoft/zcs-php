<?php

/**
 * A Zimbra distribution list.
 *
 * @author LiberSoft <info@libersoft.it>
 * @license http://www.gnu.org/licenses/gpl.txt
 */
class ZimbraDistributionList extends lsZimbraObject
{

    private $members = array();

    public function __construct($list)
    {
        parent::__construct($list);

        foreach ($list->children()->dlm as $data) {
            $this->members[] = (string) $data;
        }
        $this->set('members', implode("\r\n", $this->members));
    }

    public function getMembers()
    {
        return implode(', ', $this->members);
    }

}
