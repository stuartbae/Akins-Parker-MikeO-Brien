<?php

namespace Swim\Entity;

class Lesson
{
   public $id;
   public $host;
   public $pool;
   public $tuition;
   public $deposit;
   public $approved;
   public $createdAt;
   public $updatedAt;



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
     * Gets the value of host.
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the value of host.
     *
     * @param mixed $host the host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Gets the value of pool.
     *
     * @return mixed
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * Sets the value of pool.
     *
     * @param mixed $pool the pool
     *
     * @return self
     */
    public function setPool($pool)
    {
        $this->pool = $pool;

        return $this;
    }

    /**
     * Gets the value of tuition.
     *
     * @return mixed
     */
    public function getTuition()
    {
        return $this->tuition;
    }

    /**
     * Sets the value of tuition.
     *
     * @param mixed $tuition the tuition
     *
     * @return self
     */
    public function setTuition($tuition)
    {
        $this->tuition = $tuition;

        return $this;
    }

    /**
     * Gets the value of deposit.
     *
     * @return mixed
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * Sets the value of deposit.
     *
     * @param mixed $deposit the deposit
     *
     * @return self
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;

        return $this;
    }

    /**
     * Gets the value of approved.
     *
     * @return mixed
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Sets the value of approved.
     *
     * @param mixed $approved the approved
     *
     * @return self
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Gets the value of createdAt.
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param mixed $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param mixed $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}


