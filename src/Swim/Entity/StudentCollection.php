<?php

namespace Swim\Entity;

use Swim\Entity\Student;
use Doctrine\Common\Collections\ArrayCollection;


class StudentCollection
{

   protected $students;
   // protected $preferences;

   public function __construct()
   {
      $this->students = new ArrayCollection();
      // $this->preferences = new ArrayCollection();
   }

   // public function getUser()
   // {
   //    return $this->user;
   // }

   // public function setUser(User $user)
   // {
   //    $this->user = $user;
   // }

   public function getStudents()
   {
      return $this->students;
   }

   // public function getPreferences()
   // {
   //    return $this->preferences;
   // }

   // public function addPreference($preference)
   // {
   //    $this->preferences->add($preference);
   // }

   // public function removePreference($preference)
   // {
   //    $this->preferences->removeElement($preference);
   // }

   public function addStudent(Student $student)
   {
      $this->students->add($student);
   }

   public function removeStudent(Student $student)
   {
      $this->students->removeElement($student);
   }


   // public function getAddress()
   // {
   //    return $this->address;
   // }

   // public function setAddress(Address $address)
   // {
   //    $this->address = $address;
   // }
}
