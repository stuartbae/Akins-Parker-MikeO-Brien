<?php

namespace Swim\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class Student
{
   public $student_id;
   public $user_id;
   public $name;
   public $birthdate;
   public $level;
   public $note;

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

    /**
     * Gets the value of user_id.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Gets the value of birthdate.
     *
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Gets the value of level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Gets the value of note.
     *
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Gets the value of created_at.
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Gets the value of schdulePrefs.
     *
     * @return mixed
     */
    public function getSchdulePrefs()
    {
        return $this->schdulePrefs;
    }
}


