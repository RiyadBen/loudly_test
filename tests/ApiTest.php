<?php

namespace App\Tests;

use App\Controller\InvitationController;
use App\Controller\TokenAuthenticatedController;
use App\Tests\BaseTestCase;
use App\Tests\Controller\InviteControllerTest;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiTest
 *
 * Misc REST API tests to ensure general behavior
 *
 * @package App\Tests
 */
class ApiTest extends BaseTestCase
{
    public function testAuthenticatedOnlyEndpoint_when_Accessing_Restricted_Endpoint_With_Invalid_Token__Authentication_Error_Response_Is_Returend()
    {
        $token = "INVALID";
        $response = $this->client->post("invites", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);

        $this->assertArrayHasKey("error", $responseData);
        $this->assertArrayHasKey("code", $responseData['error']);
        $this->assertArrayHasKey("message", $responseData['error']);
        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $responseData['error']['code']);
        $this->assertEquals("Unauthorized.", $responseData['error']['message']);
    }

    public function test_Not_Found_when_Trying_To_Access_Nonexistent_Endpoint____Error_Response_Is_Returned()
    {
        $response = $this->client->get("nonexistent-endpoint");

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getBody(), true);

        $this->assertArrayHasKey("error", $responseData);
        $this->assertArrayHasKey("code", $responseData['error']);
        $this->assertArrayHasKey("message", $responseData['error']);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $responseData['error']['code']);
        $this->assertEquals("Not Found", $responseData['error']['message']);
    }

    /**
     * Classes implementing TokenAuthenticatedController are considered as JWT protected
     * Check if REST API involved classes are JWT secured
     */
    public function test_RestApiControllersAreJWTSecured()
    {
        $interfaces = class_implements(InvitationController::class);
        $this->assertTrue(isset($interfaces['App\Controller\TokenAuthenticatedController']));
    }
}
