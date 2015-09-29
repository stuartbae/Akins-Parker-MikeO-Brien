<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use Swim\Entity\Address;

/**
 * Like repository
 */
class AddressRepository implements RepositoryInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \MusicBox\Repository\ArtistRepository
     */

    /**
     * @var \MusicBox\Repository\UserRepository
     */

    // public function __construct(Connection $db, $artistRepository, $userRepository)
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Saves the address to the database.
     *
     * @param \MusicBox\Entity\Like $address
     */
    public function save($address)
    {
        $addressData = array(
            'artist_id' => $address->getArtist()->getId(),
            'user_id' => $address->getUser()->getId(),
        );

        if ($address->getId()) {
            $this->db->update('address', $addressData, array('address_id' => $address->getId()));
        } else {
            // The address is new, note the creation timestamp.
            $addressData['created_at'] = time();

            $this->db->insert('address', $addressData);
            // Get the id of the newly created address and set it on the entity.
            $id = $this->db->lastInsertId();
            $address->setId($id);
        }
    }

    /**
     * Deletes the address.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        return $this->db->delete('', array('address_id' => $id));
    }

    /**
     * Returns the total number of address.
     *
     * @return integer The total number of address.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(address_id) FROM addresses');
    }

    /**
     * Returns a address matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\Like|false A address if found, false otherwise.
     */
    public function find($id)
    {
        $addressData = $this->db->fetchAssoc('SELECT * FROM addresses WHERE address_id = ?', array($id));
        return $addressData ? $this->buildAddress($addressData) : FALSE;
    }


    public function findByUser($id)
    {
        $addressData = $this->db->fetchAssoc('SELECT * FROM addresses WHERE user_id = ?', array($id));
        return $addressData ? $this->buildAddress($addressData) : FALSE;
    }




    /**
     * Returns a collection of address.
     *
     * @param integer $limit
     *   The number of address to return.
     * @param integer $offset
     *   The number of address to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of address, keyed by address id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        $orderBy = $orderBy ? $orderBy : 'starts_at';
        return $this->getaddress(array(), $limit, $offset, $orderBy);
    }

    public function findAllOpen()
    {

        return $this->db->fetchAll('SELECT address_id, starts_at FROM addresses WHERE closed=0');
    }

    /**
     * Returns a collection of address for the given artist id.
     *
     * @param integer $artistId
     *   The artist id.
     * @param integer $limit
     *   The number of address to return.
     * @param integer $offset
     *   The number of address to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of address, keyed by address id.
     */
    public function findAllByArtist($artistId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'artist_id' => $artistId,
        );
        return $this->getaddress($conditions, $limit, $offset, $orderBy);
    }


    /**
     * Returns a collection of address for the given user id.
     *
     * @param $userId
     *   The user id.
     * @param integer $limit
     *   The number of address to return.
     * @param integer $offset
     *   The number of address to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of address, keyed by address id.
     */
    public function findAllByUser($userId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'user_id' => $userId,
        );
        return $this->getaddress($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of address for the given conditions.
     *
     * @param integer $limit
     *   The number of address to return.
     * @param integer $offset
     *   The number of address to skip.
     * @param $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of address, keyed by address id.
     */
    protected function getaddress(array $conditions, $limit, $offset, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('starts_at' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('l.*')
            ->from('addresses', 'l')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('l.' . key($orderBy), current($orderBy));
        $parameters = array();
        foreach ($conditions as $key => $value) {
            $parameters[':' . $key] = $value;
            $where = $queryBuilder->expr()->eq('l.' . $key, ':' . $key);
            $queryBuilder->andWhere($where);
        }
        $queryBuilder->setParameters($parameters);
        $statement = $queryBuilder->execute();
        $addressData = $statement->fetchAll();

        $address = array();
        foreach ($addressData as $addressData) {
            $addressId = $addressData['address_id'];
            $address[$addressId] = $this->buildAddress($addressData);
        }
        return $address;
    }

    /**
     * Instantiates a address entity and sets its properties using db data.
     *
     * @param array $addressData
     *   The array of db data.
     *
     * @return \MusicBox\Entity\Like
     */
    protected function buildAddress($addressData)
    {
        // Load the related address and host
        // $address = $this->addressRepository->find($addressData['address_id']);

        $address = new Address();
        $address->street = $addressData['street'];
        // $address->setAddress($address);
        return $address;
    }
}
