<?php

namespace Swim\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Swim\Entity\User;
use Swim\Entity\Address;
use Swim\Entity\Student;
use Swim\Entity\Pool;
use Doctrine\Common\Collections\ArrayCollection;


class HostRegistration
{

   /**
   * @Assert\Type(type="Swim\Entity\User")
   * @Assert\Valid()
   */
   protected $user;


   protected $students;

   protected $preferences;

   public $pool;
   public $payment;

   /**
   * @Assert\Type(type="Swim\Entity\Address")
   * @Assert\Valid()
   */
   protected $address;

   public function __construct()
   {
      $this->students = new ArrayCollection();
      $this->preferences = new ArrayCollection();
   }

   public function getUser()
   {
      return $this->user;
   }

   public function setUser(User $user)
   {
      $this->user = $user;
   }

   public function getStudents()
   {
      return $this->students;
   }

   public function getPreferences()
   {
      return $this->preferences;
   }

   public function addPreference($preference)
   {
      $this->preferences->add($preference);
   }

   public function removePreference($preference)
   {
      $this->preferences->removeElement($preference);
   }

   public function addStudent(Student $student)
   {
      $this->students->add($student);
   }

   public function removeStudent(Student $student)
   {
      $this->students->removeElement($student);
   }
   // public function setStudents(Student $students)
   // {
   //    $this->students[] = $students;
   // }

   public function getAddress()
   {
      return $this->address;
   }

   public function setAddress(Address $address)
   {
      $this->address = $address;
   }
}
