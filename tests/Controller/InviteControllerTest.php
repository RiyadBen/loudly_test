<?php

namespace App\Tests\Controller;

use App\Entity\Invitation;
use App\Entity\User;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class InviteControllerTest extends BaseTestCase
{


    public function test__Create_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $data = [
            "receiver"=>$receiver->getEmail(),
            "message"=>"Test Message",
        ];
        $response = $this->client->post("invites", [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken()
            ]
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function test__Create_Invite_Bad_Request()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");
        $data = [
            "receiver"=>$receiver->getEmail(),
        ];
        $response = $this->client->post("invites", [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken()
            ]
        ]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_Get_User_Invites()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");
        $sender = $this->createTestUser("sender@email.com","123456");
        $data = [
            "receiver"=>$receiver->getEmail(),
            "message"=>"Test"
        ];
        $response = $this->client->post("invites", [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken($sender)
            ]
        ]);
        $response = $this->client->get("invites", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken($sender)
            ]
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey("sent", $responseData);
        $this->assertArrayHasKey("received", $responseData);
        $this->assertArrayHasKey(0, $responseData["sent"]);
        $this->assertEquals("receiver@email.com", $responseData['sent'][0]['receiver']);
    }
    public function test_User_Accept_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");
        $response = $this->setInviteStatusHTTP($invite,"Accepted",$receiver);
        $this->assertEquals(Response::HTTP_OK,$response->getStatusCode());

        $response = $this->getTestInviteHTTP($invite,$receiver);
        $this->assertEquals(Response::HTTP_OK,$response->getStatusCode());
        $responseData =json_decode($response->getBody(),true);
        $this->assertArrayHasKey("status",$responseData);
        $this->assertEquals("Accepted",$responseData['status']);

    }

    public function test_User_Accept_Cancelled_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Cancelled",$this->testUser);
        $response = $this->setInviteStatusHTTP($invite,"Accepted",$receiver);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,$response->getStatusCode());
        $responseData = json_decode($response->getBody(),true);
        $this->assertArrayHasKey("error",$responseData);
        $this->assertArrayHasKey("message",$responseData["error"]);
        $this->assertEquals($responseData["error"]["message"],Invitation::ACCEPT_CANCELLED_MSG);

    }

    public function test_User_Reject_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Rejected",$receiver);
        $this->assertEquals(Response::HTTP_OK,$response->getStatusCode());

        $response = $this->getTestInviteHTTP($invite,$receiver);
        $this->assertEquals(Response::HTTP_OK,$response->getStatusCode());
        $responseData =json_decode($response->getBody(),true);
        $this->assertArrayHasKey("status",$responseData);
        $this->assertEquals("Rejected",$responseData['status']);
    }

    public function test_User_Reject_Cancelled_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Cancelled",$this->testUser);
        $response = $this->setInviteStatusHTTP($invite,"Rejected",$receiver);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,$response->getStatusCode());
        $responseData = json_decode($response->getBody(),true);
        $this->assertArrayHasKey("error",$responseData);
        $this->assertArrayHasKey("message",$responseData["error"]);
        $this->assertEquals($responseData["error"]["message"],Invitation::REJECT_CANCELLED_MSG);
    }
    public function test_Receiver_Cancel_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Cancelled",$receiver);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,$response->getStatusCode());
        $responseData = json_decode($response->getBody(),true);
        $this->assertArrayHasKey("error",$responseData);
        $this->assertArrayHasKey("message",$responseData["error"]);
        $this->assertEquals($responseData["error"]["message"],Invitation::SENDER_CANCEL_MSG);
    }
    public function test_Sender_Accept_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Accepted",$this->testUser);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,$response->getStatusCode());
        $responseData = json_decode($response->getBody(),true);
        $this->assertArrayHasKey("error",$responseData);
        $this->assertArrayHasKey("message",$responseData["error"]);
        $this->assertEquals($responseData["error"]["message"],Invitation::RECEIVER_ACCEPT_MSG);
    }

    public function test_Sender_Reject_Invite()
    {
        $receiver = $this->createTestUser("receiver@email.com","123456");

        $invite = $this->createTestInviteHTTP($this->testUser,$receiver,"Test Message");

        $response = $this->setInviteStatusHTTP($invite,"Rejected",$this->testUser);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,$response->getStatusCode());
        $responseData = json_decode($response->getBody(),true);
        $this->assertArrayHasKey("error",$responseData);
        $this->assertArrayHasKey("message",$responseData["error"]);
        $this->assertEquals($responseData["error"]["message"],Invitation::RECEIVER_REJECT_MSG);
    }

    public function createTestInviteHTTP($sender,$receiver,$message){
        $data = [
            "receiver"=>$receiver->getEmail(),
            "message"=>$message,
        ];
        $response = $this->client->post("invites", [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken($sender)
            ]
        ]);
        $invite = json_decode($response->getBody(),true);
        return $invite;
    }
    public function getTestInviteHTTP($invite,$user){
        $response = $this->client->get("invites/{$invite['data']['id']}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken($user)
            ]
        ]);
        return $response;
    }
    public function setInviteStatusHTTP($invite,$status,$user){
        $data = ["status"=>$status];
        $response = $this->client->post("invites/{$invite['data']['id']}", [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getValidToken($user)
            ]
        ]);
        return $response;
    }
}
