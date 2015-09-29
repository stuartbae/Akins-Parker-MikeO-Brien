<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use MusicBox\Entity\Like;

/**
 * Like repository
 */
class CouponRepository implements RepositoryInterface
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

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Saves the coupon to the database.
     *
     * @param \MusicBox\Entity\Like $coupon
     */
    public function save($coupon)
    {

    }

    /**
     * Deletes the coupon.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        return $this->db->delete('', array('coupon_id' => $id));
    }

    /**
     * Returns the total number of coupons.
     *
     * @return integer The total number of coupons.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(coupon_id) FROM coupons');
    }

    /**
     * Returns a coupon matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\Like|false A coupon if found, false otherwise.
     */
    public function find($id)
    {
        $couponData = $this->db->fetchAssoc('SELECT * FROM coupons WHERE coupon_id = ?', array($id));
        return $couponData ? $this->buildGroup($couponData) : FALSE;
    }


    /**
     * Returns a collection of coupons.
     *
     * @param integer $limit
     *   The number of coupons to return.
     * @param integer $offset
     *   The number of coupons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of coupons, keyed by coupon id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        $orderBy = $orderBy ? $orderBy : 'expire_at';
        return $this->getcoupon(array(), $limit, $offset, $orderBy);
    }

    public function isExpired($code)
    {
        $dateDiff =  $this->db->fetchColumn('SELECT datediff(expire_at, curdate()) FROM coupons WHERE code=?', array($code));
        return $dateDiff >= 0 ? true : false;
    }

    /**
     * Returns a collection of coupons for the given artist id.
     *
     * @param integer $artistId
     *   The artist id.
     * @param integer $limit
     *   The number of coupons to return.
     * @param integer $offset
     *   The number of coupons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of coupons, keyed by coupon id.
     */
    public function findAllByArtist($artistId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'artist_id' => $artistId,
        );
        return $this->getcoupon($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of coupons for the given user id.
     *
     * @param $userId
     *   The user id.
     * @param integer $limit
     *   The number of coupons to return.
     * @param integer $offset
     *   The number of coupons to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of coupons, keyed by coupon id.
     */
    public function findAllByUser($userId, $limit, $offset = 0, $orderBy = array())
    {
        $conditions = array(
            'user_id' => $userId,
        );
        return $this->getcoupon($conditions, $limit, $offset, $orderBy);
    }

    /**
     * Returns a collection of coupons for the given conditions.
     *
     * @param integer $limit
     *   The number of coupons to return.
     * @param integer $offset
     *   The number of coupons to skip.
     * @param $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of coupons, keyed by coupon id.
     */
    protected function getcoupon(array $conditions, $limit, $offset, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('starts_at' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('l.*')
            ->from('coupons', 'l')
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
        $couponsData = $statement->fetchAll();

        $coupons = array();
        foreach ($couponsData as $couponData) {
            $couponId = $couponData['coupon_id'];
            $coupons[$couponId] = $this->buildGroup($couponData);
        }
        return $coupons;
    }

    /**
     * Instantiates a coupon entity and sets its properties using db data.
     *
     * @param array $couponData
     *   The array of db data.
     *
     * @return \MusicBox\Entity\Like
     */
    protected function buildGroup($couponData)
    {
        // Load the related artist and user.
        // $artist = $this->artistRepository->find($couponData['artist_id']);
        // $user = $this->userRepository->find($couponData['user_id']);

        // $coupon = new Like();
        // $coupon->setId($couponData['coupon_id']);
        // $coupon->setArtist($artist);
        // $coupon->setUser($user);
        // $createdAt = new \DateTime('@' . $couponData['created_at']);
        // $coupon->setCreatedAt($createdAt);
        // return $coupon;
        return $couponData;
    }
}
