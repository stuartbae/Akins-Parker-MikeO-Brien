<?php

namespace Swim\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class SchedulePref
{
  protected $pref1;
  protected $pref2;
  protected $pref3;

public function __construct()
{
   $this->pref1 = new ArrayCollection();
   $this->pref2 = new ArrayCollection();
   $this->pref3 = new ArrayCollection();

}


    /**
     * Gets the value of pref1.
     *
     * @return mixed
     */
    public function getPref1()
    {
        return $this->pref1;
    }

    /**
     * Gets the value of pref2.
     *
     * @return mixed
     */
    public function getPref2()
    {
        return $this->pref2;
    }

    /**
     * Gets the value of pref3.
     *
     * @return mixed
     */
    public function getPref3()
    {
        return $this->pref3;
    }
}
