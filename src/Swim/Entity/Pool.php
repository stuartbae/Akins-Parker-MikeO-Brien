<?php

namespace Swim\Entity;

class Pool
{
   public $pool_id;
   public $address;
   protected $file;
   public $accessinfo;


   public function getFile() {
       return $this->file;
   }

   public function setFile(UploadedFile $file = null)
   {
       $this->file = $file;
   }
}
