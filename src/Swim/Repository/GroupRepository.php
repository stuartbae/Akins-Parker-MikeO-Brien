<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use Swim\Entity\Group;

/**
 * Like repository
 */
class GroupRepository implements RepositoryInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \MusicBox\Repository\ArtistRepository
     */
    protected $userRepository;

    /**
     * @var \MusicBox\Repository\UserRepository
     */
    protected $lessonRepository;

    // public function __construct(Connection $db, $artistRepository, $userRepository)
    public function __construct(Connection $db, $lessonRepository, $userRepository)
    {
        $this->db = $db;
        $this->lessonRepository = $lessonRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Saves the group to the database.
     *
     * @param \MusicBox\Entity\Like $group
     */
    public function save($group)
    {
        $groupData = array(
            'artist_id' => $group->getArtist()->getId(),
            'user_id' => $group->getUser()->getId(),
        );

        if ($group->getId()) {
            $this->db->update('groups', $groupData, array('group_id' => $group->getId()));
        } else {
            // The group is new, note the creation timestamp.
            $groupData['created_at'] = time();

            $this->db->insert('groups', $groupData);
            // Get the id of the newly created group and set it on the entity.
            $id = $this->db->lastInsertId();
            $group->setId($id);
        }
    }

    /**
     * Deletes the group.
     *
     * @param integer $id
     */
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

    public function findByCode($code)
    {
        $groupData = $this->db->fetchAssoc('SELECT * FROM groups WHERE group_code = ?', array($code));
        return $groupData ? $this->buildGroup($groupData) : FALSE;
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

    public function findAllOpen()
    {

        return $this->db->fetchAll('SELECT group_id, starts_at FROM groups WHERE closed=0');
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
            $orderBy = array('starts_at' => 'ASC');
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
            $groups[$groupId] = $this->buildGroup($groupData);
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
    protected function buildGroup($groupData)
    {
        // Load the related lesson and host
        $lesson = $this->lessonRepository->find($groupData['lesson_id']);
        $host = $lesson->getHost();

        $group = new Group();
        $group->setId($groupData['group_id']);
        $group->setLesson($lesson);
        $group->setHost($host);
        $group->setCode($groupData['group_code']);
        $startsAt = new \DateTime('@' . $groupData['starts_at']);
        $group->setStartsAt($startsAt);
        return $group;
    }
}
