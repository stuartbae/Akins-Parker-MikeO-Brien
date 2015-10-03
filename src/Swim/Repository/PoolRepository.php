<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use Swim\Entity\Pool;

/**
 * Like repository
 */
class PoolRepository implements RepositoryInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \MusicBox\Repository\ArtistRepository
     */
    protected $addressRepository;

    /**
     * @var \MusicBox\Repository\UserRepository
     */

    // public function __construct(Connection $db, $artistRepository, $userRepository)
    public function __construct(Connection $db, $addressRepository)
    {
        $this->db = $db;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Saves the pool to the database.
     *
     * @param \MusicBox\Entity\Like $pool
     */
    public function save($pool)
    {
        $poolData = array(
            'address_id' => $pool->getAddress()->getId(),
            'access_info' => $pool->getAccessInfo(),
        );

        if ($pool->getId()) {
            $this->db->update('pools', $poolData, array('pool_id' => $pool->getId()));
            $newFile = $this->handleFileUpload($item);
            if ($newFile) {
                $poolData['image'] = $pool->getImage();
            }
        } else {
            // The pool is new, note the creation timestamp.
            $poolData['created_at'] = time();

            $this->db->insert('pools', $poolData);
            // Get the id of the newly created pool and set it on the entity.
            $id = $this->db->lastInsertId();
            $pool->setId($id);

            // If a new image was uploaded, update the pool with the new
            // filename.
            $newFile = $this->handleFileUpload($pool);
            if ($newFile) {
                $newData = array('image' => $pool->getImage());
                $this->db->update('pools', $newData, array('pool_id' => $id));
            }
        }

    }

    /**
     * Deletes the pool.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        return $this->db->delete('', array('pool_id' => $id));
    }

    /**
     * Returns the total number of pools.
     *
     * @return integer The total number of pools.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(pool_id) FROM pools');
    }

    /**
     * Returns a pool matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\Like|false A pool if found, false otherwise.
     */
    public function find($id)
    {
        $poolData = $this->db->fetchAssoc('SELECT * FROM pools WHERE pool_id = ?', array($id));
        return $poolData ? $this->buildPool($poolData) : FALSE;
    }




    /**
     * Returns a collection of pools.
     *
     * @param integer $limit
     *   The number of pools to return.
     * @param integer $offset
     *   The number of pools to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of pools, keyed by pool id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        $orderBy = $orderBy ? $orderBy : 'starts_at';
        return $this->getpool(array(), $limit, $offset, $orderBy);
    }

    public function findAllOpen()
    {

        return $this->db->fetchAll('SELECT pool_id, starts_at FROM pools WHERE closed=0');
    }


    /**
     * Returns a collection of pools for the given user id.
     *
     * @param $userId
     *   The user id.
     * @param integer $limit
     *   The number of pools to return.
     * @param integer $offset
     *   The number of pools to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of pools, keyed by pool id.
     */
    public function findAllByUser($userId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'user_id' => $userId,
        );
        return $this->getpool($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of pools for the given conditions.
     *
     * @param integer $limit
     *   The number of pools to return.
     * @param integer $offset
     *   The number of pools to skip.
     * @param $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of pools, keyed by pool id.
     */
    protected function getpool(array $conditions, $limit, $offset, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('starts_at' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('l.*')
            ->from('pools', 'l')
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
        $poolsData = $statement->fetchAll();

        $pools = array();
        foreach ($poolsData as $poolData) {
            $poolId = $poolData['pool_id'];
            $pools[$poolId] = $this->buildPool($poolData);
        }
        return $pools;
    }

    /**
     * Instantiates a pool entity and sets its properties using db data.
     *
     * @param array $poolData
     *   The array of db data.
     *
     * @return \MusicBox\Entity\Like
     */
    protected function buildPool($poolData)
    {
        // Load the related pool and host
        $address = $this->addressRepository->find($poolData['address_id']);
        // $pool = $this->poolRepository->find($poolData['pool_id']);

        $pool = new Pool();
        $pool->setPoolId($poolData['pool_id']);
        // $pool->setPool($pool);
        $pool->setAddress($address);
        // $pool->setFile($poolData['file']);
        $pool->setAccessinfo($poolData['access_info']);
        return $pool;
    }
}
