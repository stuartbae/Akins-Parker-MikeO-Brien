<?php

namespace Swim\Repository;

use Doctrine\DBAL\Connection;
use Swim\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * User repository
 */
class UserRepository implements RepositoryInterface, UserProviderInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder
     */
    protected $encoder;

    protected $addressRepository;

    public function __construct(Connection $db, $encoder, $addressRepository)
    {
        $this->db = $db;
        $this->encoder = $encoder;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Saves the user to the database.
     *
     * @param \MusicBox\Entity\User $user
     */
    public function save($user)
    {
        $userData = array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'spouse_firstname' => $user->getSpouseFirstName(),
            'spouse_lastname' => $user->getSpouseLastName(),
            'mobile' => $user->getMobile(),
            'home' => $user->getHome(),
        );
        // If the password was changed, re-encrypt it.
        if (strlen($user->getPassword()) != 88) {
            $userData['salt'] = uniqid(mt_rand());
            $userData['password'] = $this->encoder->encodePassword($user->getPassword(), $userData['salt']);
        }

        // dump($userData);exit();

        if ($user->getUserId()) {
            // If a new image was uploaded, make sure the filename gets set.
            // $newFile = $this->handleFileUpload($user);
            // if ($newFile) {
            //     $userData['image'] = $user->getImage();
            // }
            try {
                $this->db->update('users', $userData, array('user_id' => $user->getUserId()));
            } catch (\Exception $e) {
                return FALSE;;
            }
        } else {
            // The user is new, note the creation timestamp.
            $userData['created_at'] = time();

            try {
                $this->db->insert('users', $userData);
                // Get the id of the newly created user and set it on the entity.
                $user_id = $this->db->lastInsertId();
                $user->setUserId($user_id);
                return $user_id;
            } catch (\Exception $e) {
                dump($e);
                return false;
                }
            }

            // If a new image was uploaded, update the user with the new
            // filename.
            // $newFile = $this->handleFileUpload($user);
            // if ($newFile) {
            //     $newData = array('image' => $user->getImage());
            //     $this->db->update('users', $newData, array('user_id' => $user_id));
            // }
            return TRUE;

    }

    /**
     * Handles the upload of a user image.
     *
     * @param \MusicBox\Entity\User $user
     *
     * @param boolean TRUE if a new user image was uploaded, FALSE otherwise.
     */
    protected function handleFileUpload($user) {
        // If a temporary file is present, move it to the correct directory
        // and set the filename on the user.
        $file = $user->getFile();
        if ($file) {
            $newFilename = $user->getUsername() . '.' . $file->guessExtension();
            $file->move(SWIM_PUBLIC_ROOT . '/img/users', $newFilename);
            $user->setFile(null);
            $user->setImage($newFilename);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Deletes the user.
     *
     * @param integer $id
     */
    public function delete($user_id)
    {
        return $this->db->delete('users', array('user_id' => $user_id));
    }

    /**
     * Returns the total number of users.
     *
     * @return integer The total number of users.
     */
    public function getCount() {
        return $this->db->fetchColumn('SELECT COUNT(user_id) FROM users');
    }

    /**
     * Returns a user matching the supplied id.
     *
     * @param integer $id
     *
     * @return \MusicBox\Entity\User|false An entity object if found, false otherwise.
     */
    public function find($user_id)
    {
        // dump($user_id);
        $userData = $this->db->fetchAssoc('SELECT * FROM users WHERE user_id = ?', array($user_id));
        return $userData ? $this->buildUser($userData) : FALSE;
    }

    public function findByUsername($username)
    {
        $userData = $this->db->fetchAssoc('SELECT * FROM users WHERE username = ?', array($username));
        return $userData ? $this->buildUser($userData) : FALSE;

    }

    public function getAddress($user_id)
    {
        // dump($user_id);
        $userAddress = $this->db->fetchAssoc('SELECT * FROM addresses WHERE billing is NULL and user_id = ?', array($user_id));
        return $userAddress ? $userAddress : FALSE;
    }

    public function insertAddress($address)
    {
       // $addressData = array(
       //  'user_id' => $address->getUserId();
       //  'street' => $address->getUserId();

       //  )
       // $this->db->insert('addresses', (array) $address);

    }

    /**
     * Returns a collection of users.
     *
     * @param integer $limit
     *   The number of users to return.
     * @param integer $offset
     *   The number of users to skip.
     * @param array $orderBy
     *   Optionally, the order by info, in the $column => $direction format.
     *
     * @return array A collection of users, keyed by user id.
     */
    public function findAll($limit, $offset = 0, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('username' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('u.*')
            ->from('users', 'u')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.' . key($orderBy), current($orderBy));
        $statement = $queryBuilder->execute();
        $usersData = $statement->fetchAll();

        $users = array();
        foreach ($usersData as $userData) {
            $userId = $userData['user_id'];
            $users[$userId] = $this->buildUser($userData);
        }

        return $users;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('u.*')
            ->from('users', 'u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->setMaxResults(1);
        $statement = $queryBuilder->execute();
        $usersData = $statement->fetchAll();
        if (empty($usersData)) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $user = $this->buildUser($usersData[0]);
        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        $user_id = $user->getUserId();
        $refreshedUser = $this->find($user_id);
        if (false === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($user_id)));
        }

        return $refreshedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return 'Swim\Entity\User' === $class;
    }

    /**
     * Instantiates a user entity and sets its properties using db data.
     *
     * @param array $userData
     *   The array of db data.
     *
     * @return \Swim\Entity\User
     */
    protected function buildUser($userData)
    {
        $address = $this->addressRepository->findByUser($userData['user_id']);
        $user = new User();
        $user->setUserId($userData['user_id']);
        $user->setUsername($userData['username']);
        $user->setSalt($userData['salt']);
        $user->setPassword($userData['password']);
        $user->setRole($userData['role']);
        $user->setFirstName($userData['firstname']);
        $user->setLastName($userData['lastname']);
        $user->setSpouseFirstName($userData['spouse_firstname']);
        $user->setSpouseLastName($userData['spouse_lastname']);
        $user->setMobile($userData['mobile']);
        $user->setHome($userData['home']);
        $user->setEmail($userData['email']);
        $user->setAddress($address);

        $createdAt = new \DateTime('@' . $userData['created_at']);
        $user->setCreatedAt($createdAt);
        return $user;
    }
}
