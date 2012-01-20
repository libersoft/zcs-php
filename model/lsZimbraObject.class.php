<?php

/**
 * A basic Zimbra object.
 *
 * @author LiberSoft <info@libersoft.it>
 * @license http://www.gnu.org/licenses/gpl.txt
 */
class lsZimbraObject
{

    private $data = array();

    public function __construct($object)
    {
        $this->data['id'] = (string) $object['id'];
        $this->data['name'] = (string) $object['name'];

        foreach ($object->children()->a as $data) {
            $key = (string) $data['n'];

            switch ($data) {
                case 'FALSE':
                    $this->data[$key] = false;
                    break;
                case 'TRUE':
                    $this->data[$key] = true;
                    break;
                default:
                    $this->data[$key] = (string) $data;
            }
        }
    }

    public function __toString()
    {
        return $this->data['name'];
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }

    public function get($name)
    {
        return $this->__get($name);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function set($name, $value)
    {
        $this->__set($name, $value);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function getAttributes()
    {
        return array_keys($this->data);
    }

}
