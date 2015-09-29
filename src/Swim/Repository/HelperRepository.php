<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;

/**
 * Like repository
 */
class HelperRepository implements RepositoryInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \MusicBox\Repository\ArtistRepository
     */
    // protected $userRepository;

    /**
     * @var \MusicBox\Repository\UserRepository
     */
    // protected $userRepository;

    // public function __construct(Connection $db, $artistRepository, $userRepository)
    public function __construct(Connection $db)
    {
        $this->db = $db;
        // $this->artistRepository = $artistRepository;
        // $this->userRepository = $userRepository;
    }

    /**
     * Saves the group to the database.
     *
     * @param \MusicBox\Entity\Like $group
     */

    public function save($entity)
    {
        # code...
    }

    /**
     * Deletes the group.
     *
     * @param integer $id
     */

    public function findAllLevel()
    {
        return $this->db->fetchAll('SELECT * FROM exp_levels');
    }

    public function delete($id)
    {
        return $this->db->delete('', array('group_id' => $id));
    }

    /**
     * Returns the total number of groups.
     *
     * @return integer The total number of groups.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(group_id) FROM groups');
    }

    /**
     * Returns a group matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\Like|false A group if found, false otherwise.
     */
    public function find($id)
    {
        $groupData = $this->db->fetchAssoc('SELECT * FROM groups WHERE group_id = ?', array($id));
        return $groupData ? $this->buildGroup($groupData) : FALSE;
    }

    /**
     * Returns a collection of groups for the given user id.
     *
     * @param integer $artistId
     *   The artist id.
     * @param integer $userId
     *   The user id.
     *
     * @return \MusicBox\Entity\Like|false A group if found, false otherwise.
     */
    public function findByLesson($lessonId)
    {
        $conditions = array(
            'artist_id' => $artistId,
            'user_id' => $userId,
        );
        $groups = $this->getgroup($conditions, 1, 0);
        if ($groups) {
            return reset($groups);
        }
    }

    /**
     * Returns a collection of groups.
     *
     * @param integer $limit
     *   The number of groups to return.
     * @param integer $offset
     *   The number of groups to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of groups, keyed by group id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        $orderBy = $orderBy ? $orderBy : 'starts_at';
        return $this->getgroup(array(), $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of groups for the given artist id.
     *
     * @param integer $artistId
     *   The artist id.
     * @param integer $limit
     *   The number of groups to return.
     * @param integer $offset
     *   The number of groups to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of groups, keyed by group id.
     */
    public function findAllByArtist($artistId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'artist_id' => $artistId,
        );
        return $this->getgroup($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of groups for the given user id.
     *
     * @param $userId
     *   The user id.
     * @param integer $limit
     *   The number of groups to return.
     * @param integer $offset
     *   The number of groups to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of groups, keyed by group id.
     */
    public function findAllByUser($userId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'user_id' => $userId,
        );
        return $this->getgroup($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of groups for the given conditions.
     *
     * @param integer $limit
     *   The number of groups to return.
     * @param integer $offset
     *   The number of groups to skip.
     * @param $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of groups, keyed by group id.
     */
    protected function getgroup(array $conditions, $limit, $offset, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('created_at' => 'DESC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('l.*')
            ->from('groups', 'l')
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
        $groupsData = $statement->fetchAll();

        $groups = array();
        foreach ($groupsData as $groupData) {
            $groupId = $groupData['group_id'];
            $groups[$groupId] = $this->buildLike($groupData);
        }
        return $groups;
    }

    /**
     * Instantiates a group entity and sets its properties using db data.
     *
     * @param array $groupData
     *   The array of db data.
     *
     * @return \MusicBox\Entity\Like
     */
    protected function buildLike($groupData)
    {
        // Load the related artist and user.
        $artist = $this->artistRepository->find($groupData['artist_id']);
        $user = $this->userRepository->find($groupData['user_id']);

        $group = new Like();
        $group->setId($groupData['group_id']);
        $group->setArtist($artist);
        $group->setUser($user);
        $createdAt = new \DateTime('@' . $groupData['created_at']);
        $group->setCreatedAt($createdAt);
        return $group;
    }
}
