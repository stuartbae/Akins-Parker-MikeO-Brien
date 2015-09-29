<?php

namespace Swim\Entity;

/**
* Group
*/
class Group
{
   private $Id;
   private $lesson;
   private $code;
   private $seats;
   private $startsAt;
   private $closed;
   private $host;

    /**
     * Gets the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * Sets the value of Id.
     *
     * @param mixed $Id the id
     *
     * @return self
     */
    public function setId($Id)
    {
        $this->Id = $Id;

        return $this;
    }

    /**
     * Gets the value of code.
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the value of code.
     *
     * @param mixed $code the code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets the value of lesson.
     *
     * @return mixed
     */
    public function getLesson()
    {
        return $this->lesson;
    }

    /**
     * Sets the value of lesson.
     *
     * @param mixed $lesson the lesson
     *
     * @return self
     */
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;

        return $this;
    }

    /**
     * Gets the value of seats.
     *
     * @return mixed
     */
    public function getSeats()
    {
        return $this->seats;
    }

    /**
     * Sets the value of seats.
     *
     * @param mixed $seats the seats
     *
     * @return self
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;

        return $this;
    }

    /**
     * Gets the value of startsAt.
     *
     * @return mixed
     */
    public function getStartsAt()
    {
        return $this->startsAt;
    }

    /**
     * Sets the value of startsAt.
     *
     * @param mixed $startsAt the starts at
     *
     * @return self
     */
    public function setStartsAt($startsAt)
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    /**
     * Gets the value of closed.
     *
     * @return mixed
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Sets the value of closed.
     *
     * @param mixed $closed the closed
     *
     * @return self
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

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
}
