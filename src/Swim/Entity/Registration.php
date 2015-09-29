<?php

namespace Swim\Entity;

use Swim\Entity\User;
use Swim\Entity\Address;
use Swim\Entity\Student;
use Swim\Entity\Group;
use Swim\Entity\Pool;
use Swim\Entity\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class Registration
{
   public $user;
   public $address;
   public $students;
   public $groups;
   public $pool;
   public $payment;

   public function __construct()
   {
      $this->students = new ArrayCollection();
      $this->groups = new ArrayCollection();
      $this->forms = new ArrayCollection();
   }

}

