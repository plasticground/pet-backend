<?php


namespace App\Traits;

/**
 * Trait HasAttributes
 * @package App\Traits
 */
trait HasAttributes
{
    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $getter = 'get' . ucfirst($name);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function __set(string $name, $value)
    {
        $setter = 'set' . ucfirst($name);

        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } else {
            $this->{$name} = $value;
        }

        return $this;
    }
}