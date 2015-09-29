<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use Swim\Entity\Lesson;

/**
 * Like repository
 */
class LessonRepository implements RepositoryInterface
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
    protected $poolRepository;

    // public function __construct(Connection $db, $artistRepository, $userRepository)
    public function __construct(Connection $db, $userRepository, $poolRepository)
    {
        $this->db = $db;
        $this->poolRepository = $poolRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Saves the lesson to the database.
     *
     * @param \MusicBox\Entity\Like $lesson
     */
    public function save($lesson)
    {
        $lessonData = array(
            'artist_id' => $lesson->getArtist()->getId(),
            'user_id' => $lesson->getUser()->getId(),
        );

        if ($lesson->getId()) {
            $this->db->update('lessons', $lessonData, array('lesson_id' => $lesson->getId()));
        } else {
            // The lesson is new, note the creation timestamp.
            $lessonData['created_at'] = time();

            $this->db->insert('lessons', $lessonData);
            // Get the id of the newly created lesson and set it on the entity.
            $id = $this->db->lastInsertId();
            $lesson->setId($id);
        }
    }

    /**
     * Deletes the lesson.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        return $this->db->delete('', array('lesson_id' => $id));
    }

    /**
     * Returns the total number of lessons.
     *
     * @return integer The total number of lessons.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(lesson_id) FROM lessons');
    }

    /**
     * Returns a lesson matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\Like|false A lesson if found, false otherwise.
     */
    public function find($id)
    {
        $lessonData = $this->db->fetchAssoc('SELECT * FROM lessons WHERE lesson_id = ?', array($id));
        return $lessonData ? $this->buildLesson($lessonData) : FALSE;
    }




    /**
     * Returns a collection of lessons.
     *
     * @param integer $limit
     *   The number of lessons to return.
     * @param integer $offset
     *   The number of lessons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of lessons, keyed by lesson id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        $orderBy = $orderBy ? $orderBy : 'starts_at';
        return $this->getlesson(array(), $limit, $offset, $orderBy);
    }

    public function findAllOpen()
    {

        return $this->db->fetchAll('SELECT lesson_id, starts_at FROM lessons WHERE closed=0');
    }

    /**
     * Returns a collection of lessons for the given artist id.
     *
     * @param integer $artistId
     *   The artist id.
     * @param integer $limit
     *   The number of lessons to return.
     * @param integer $offset
     *   The number of lessons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of lessons, keyed by lesson id.
     */
    public function findAllByArtist($artistId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'artist_id' => $artistId,
        );
        return $this->getlesson($conditions, $limit, $offset, $orderBy);
    }


    /**
     * Returns a collection of lessons for the given user id.
     *
     * @param $userId
     *   The user id.
     * @param integer $limit
     *   The number of lessons to return.
     * @param integer $offset
     *   The number of lessons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of lessons, keyed by lesson id.
     */
    public function findAllByUser($userId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'user_id' => $userId,
        );
        return $this->getlesson($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of lessons for the given conditions.
     *
     * @param integer $limit
     *   The number of lessons to return.
     * @param integer $offset
     *   The number of lessons to skip.
     * @param $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of lessons, keyed by lesson id.
     */
    protected function getlesson(array $conditions, $limit, $offset, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('starts_at' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('l.*')
            ->from('lessons', 'l')
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
        $lessonsData = $statement->fetchAll();

        $lessons = array();
        foreach ($lessonsData as $lessonData) {
            $lessonId = $lessonData['lesson_id'];
            $lessons[$lessonId] = $this->buildLesson($lessonData);
        }
        return $lessons;
    }

    /**
     * Instantiates a lesson entity and sets its properties using db data.
     *
     * @param array $lessonData
     *   The array of db data.
     *
     * @return \MusicBox\Entity\Like
     */
    protected function buildLesson($lessonData)
    {
        // Load the related lesson and host
        $host = $this->userRepository->find($lessonData['host_id']);
        $pool = $this->poolRepository->find($lessonData['pool_id']);

        $lesson = new Lesson();
        $lesson->setId($lessonData['lesson_id']);
        // $lesson->setLesson($lesson);
        $lesson->setHost($host);
        $lesson->setPool($pool);
        $lesson->setTuition($lessonData['tuition']);
        $lesson->setDeposit($lessonData['deposit']);
        $lesson->setApproved($lessonData['approved']);

        $createdAt = new \DateTime('@' . $lessonData['created_at']);
        $lesson->setCreatedAt($createdAt);
        $updatedAt = new \DateTime('@' . $lessonData['updated_at']);
        $lesson->setUpdatedAt($updatedAt);
        return $lesson;
    }
}
