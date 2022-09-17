<?php

namespace App\Tests;


use App\Entity\Invitation;
use App\Entity\User;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseTestCase extends KernelTestCase
{
    const TEST_USER_PASSWORD = "test123";
    const TEST_USER_EMAIL = "rest@jwtrestapi.com";

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var User
     */
    protected $testUser;

    public function setUp() : void
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:8000/api/',
            'exceptions' => false
        ]);

        $container = $this->getPrivateContainer();

        $this->em = $container
            ->get('doctrine')
            ->getManager();

        $this->truncateTables();

        $this->testUser = $this->createTestUser();
    }

    private function truncateTables()
    {
        $em = $this->em;

        $query = $em->createQuery('DELETE App:Invitation i WHERE 1 = 1');
        $query->execute();

        $query = $em->createQuery('DELETE App:User u WHERE 1 = 1');
        $query->execute();

        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    protected function createTestUser($email = self::TEST_USER_EMAIL, $password = self::TEST_USER_PASSWORD)
    {
        $container = $this->getPrivateContainer();
        $userService = $container
            ->get('App\Service\UserService');
        $data = [
            "email"=>$email,
            "password"=>$password
        ];

        return $userService->createUser($data);
    }

    /**
     * @param $sender User
     * @param $receiver User
     * @param $message string
     * @param $status string
     * @return Invitation|string
     */
    protected function createTestInvite($sender,$receiver,$message,$status)
    {
        $container = $this->getPrivateContainer();
        $inviteService = $container
            ->get('App\Service\InvitationService');

        return $inviteService->createInvite([
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $message,
            'status' => $status
        ]);
    }


    /**
     * Create valid JWT token for given (if any) or this user
     *
     * @param User|null $user
     * @return string Valid JWT token to use for REST API endpoints authentication
     */
    protected function getValidToken(User $user = null)
    {
        if (!$user) {
            $user = $this->testUser;
        }

        $container = $this->getPrivateContainer();
        $authService = $container
            ->get('App\Service\AuthService');

        $jwt = $authService->authenticate([
            'email' => $user->getEmail()
        ]);

        return $jwt;
    }

    private function getPrivateContainer()
    {
        self::bootKernel();

        // returns the real and unchanged service container
        //$container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        return $container;
    }

    protected function tearDown() : void
    {
        parent::tearDown();
    }
}