<?php

/**
 * A Zimbra distribution list.
 *
 * @author Matteo Giordano <mg@libersoft.it>
 */
class DistributionList extends lsZimbraObject
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
