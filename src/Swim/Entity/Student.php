<?php

namespace Swim\Entity;

class Student
{
   public $student_id;
   public $user_id;
   public $name;
   public $birthdate;
   public $level;
   public $note;
   public $created_at;


   public function getStudentId()
   {
      return $this->student_id;
   }

   public function setStudentId($id)
   {
      $this->student_id = $id;
   }

   public function getName()
   {
      return $this->name;
   }

   public function setNmae($name)
   {
      $this->name = $name;
   }
}


