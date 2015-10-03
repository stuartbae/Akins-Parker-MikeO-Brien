<?php

namespace Swim\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Pool
{
   protected $pool_id;
   protected $address;
   protected $file;
   protected $image;
   protected $accessinfo;


    /**
     * Gets the value of pool_id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->pool_id;
    }

    /**
     * Sets the value of pool_id.
     *
     * @param mixed $pool_id the pool id
     *
     * @return self
     */
    public function setId($pool_id)
    {
        $this->pool_id = $pool_id;

        return $this;
    }

    /**
     * Gets the value of address.
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the value of address.
     *
     * @param mixed $address the address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets the value of accessinfo.
     *
     * @return mixed
     */
    public function getAccessinfo()
    {
        return $this->accessinfo;
    }

    /**
     * Sets the value of accessinfo.
     *
     * @param mixed $accessinfo the accessinfo
     *
     * @return self
     */
    public function setAccessinfo($accessinfo)
    {
        $this->accessinfo = $accessinfo;

        return $this;
    }



    public function getFile() {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function getImage() {
        // Make sure the image is never empty.
        if (empty($this->image)) {
            $this->image = 'placeholder.gif';
        }

        return $this->image;
    }

    public function setImage($image) {
        $this->image = $image;
    }
}
