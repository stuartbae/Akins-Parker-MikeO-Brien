<?php

namespace Swim\Entity;

class Address
{
   protected $id;
   protected $street;
   protected $street2;
   protected $city;
   protected $state;
   protected $zip;
   protected $type;

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param mixed $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of street.
     *
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the value of street.
     *
     * @param mixed $street the street
     *
     * @return self
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Gets the value of street2.
     *
     * @return mixed
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Sets the value of street2.
     *
     * @param mixed $street2 the street2
     *
     * @return self
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * Gets the value of city.
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the value of city.
     *
     * @param mixed $city the city
     *
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Gets the value of state.
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the value of state.
     *
     * @param mixed $state the state
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets the value of zip.
     *
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the value of zip.
     *
     * @param mixed $zip the zip
     *
     * @return self
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Gets the value of type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param mixed $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
