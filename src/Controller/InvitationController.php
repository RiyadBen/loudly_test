<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\User;
use App\Service\AuthService;
use App\Service\InvitationService;
use App\Service\ResponseErrorDecoratorService;
use App\Service\UserService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\TokenAuthenticatedController;
use Symfony\Component\Security\Core\Security;

/**
 * Class InvitationController
 * @package App\Controller
 */
class InvitationController extends AbstractController implements TokenAuthenticatedController
{

    public function __construct(Security $security,AuthService $authService)
    {
        $this->security = $security;
        $this->authService = $authService;
        $this->current_user = $authService->getDecodedAuthToken();
    }

    /**
     * Creates new invite by given name (if not exists)
     *
     * @Route("/api/invites",methods={"POST","HEAD"})
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param ResponseErrorDecoratorService $errorDecorator
     * @return JsonResponse
     */
    public function createInvite(
        Request $request,
        InvitationService $invitationService,
        UserService $userService,
        ResponseErrorDecoratorService $errorDecorator
    )
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        $data["sender"] = $userService->getUser($this->current_user['email']);
        $data["receiver"] = $userService->getUser($data['receiver']);
        if (is_null($data) || !isset($data['receiver']) || !isset($data['message'])) {
            $status = JsonResponse::HTTP_BAD_REQUEST;
            $data = $errorDecorator->decorateError(
                JsonResponse::HTTP_BAD_REQUEST, "Invalid JSON format"
            );

            return new JsonResponse($data, $status);
        }

        $result = $invitationService->createInvite($data);
        if ($result instanceof Invitation) {
            $status = JsonResponse::HTTP_CREATED;
            $data = [
                'data' => [
                    'id' => $result->getId(),
                    'sender' => $result->getSender()->getEmail(),
                    'receiver' => $result->getReceiver()->getEmail(),
                    'message' => $result->getMessage(),
                    'status' => $result->getStatusAsString(),
                ]
            ];
        } else {
            $status = JsonResponse::HTTP_BAD_REQUEST;
            $data = $errorDecorator->decorateError($status, $result);
        }

        return new JsonResponse($data, $status);
    }

    /**
     * @Route("/api/invites",methods={"GET","HEAD"})
     *
     * @return JsonResponse List of invites of current user
     */
    public function getInvites(
        InvitationService $invitationService,
        UserService $userService,
        ResponseErrorDecoratorService $errorDecorator)
    {
        $user = $userService->getUser($this->current_user['email']);
        try{

            $myInvites = $invitationService->getMyInvites($user);
            $sentInvites = $invitationService->getSentInvites($user);

            $sentArr = [];
            foreach ($sentInvites as $invite) {
                $sentArr[] = [
                    "id"=> $invite->getId(),
                    "receiver"=> $invite->getReceiver()->getEmail(),
                    "sender"=> $invite->getSender()->getEmail(),
                    "message" => $invite->getMessage(),
                    "status" => $invite->getStatusAsString(),
                ];
            }
            $myInvitesArr = [];
            foreach ($myInvites as $invite) {
                $myInvitesArr[] = [
                    "id"=> $invite->getId(),
                    "receiver"=> $invite->getReceiver()->getEmail(),
                    "sender"=> $invite->getSender()->getEmail(),
                    "message" => $invite->getMessage(),
                    "status" => $invite->getStatusAsString(),
                ];
            }
            $status = JsonResponse::HTTP_OK;
            $data = [
                "sent"=>$sentArr,
                "received"=>$myInvitesArr
            ];
        }
        catch (\Exception $ex){
            $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
            $data = $errorDecorator->decorateError($status, "Cannot Get Invites");
        }


        return new JsonResponse($data, $status);
    }

    /**
     * @Route("/api/invites/{id}",methods={"POST","HEAD"})
     *
     * @param Invitation $invite
     * @param InvitationService $invitationService
     * @param ResponseErrorDecoratorService $errorDecorator
     * @return JsonResponse
     */
    public function setInviteStatus(
        Invitation $invite,
        Request $request,
        InvitationService $invitationService,
        ResponseErrorDecoratorService $errorDecorator
    )
    {

        $data = $request->getContent();
        $data = json_decode($data, true);
        $status = $data["status"];
        switch ($status){
            case "Accepted": $result = $invitationService->acceptInvitation($invite,$this->current_user["email"]);break;
            case "Rejected": $result = $invitationService->rejectInvitation($invite,$this->current_user["email"]);break;
            case "Cancelled": $result = $invitationService->cancelInvitation($invite,$this->current_user["email"]);break;
            default : $result = "Wrong Status Given";
        }
        if ($result instanceof Invitation) {
            $status = JsonResponse::HTTP_OK;
            $data = [ "data"=> [
                "id" => $invite->getId(),
                "sender"=>$invite->getSender()->getEmail(),
                "receiver"=>$invite->getReceiver()->getEmail(),
                "status"=>$invite->getStatusAsString(),
                "message"=>$invite->getMessage()
                ]
            ];
        } else {
            $status = JsonResponse::HTTP_BAD_REQUEST;
            $data = $errorDecorator->decorateError($status, $result);
        }

        return new JsonResponse($data, $status);
    }

    /**
     * @Route("/api/invites/{id}",methods={"GET","HEAD"})
     *
     * @param Invitation $invite
     * @param InvitationService $invitationService
     * @param ResponseErrorDecoratorService $errorDecorator
     * @return JsonResponse
     */
    public function getInvite(
        Invitation $invite,
        Request $request,
        InvitationService $invitationService,
        ResponseErrorDecoratorService $errorDecorator
    )
    {

        $result = $invitationService->getInvite($invite,$this->current_user["email"]);
        if ($result instanceof Invitation) {
            $status = JsonResponse::HTTP_OK;
            $data = [
                "id" => $result->getId(),
                "sender"=>$result->getSender()->getEmail(),
                "receiver"=>$result->getReceiver()->getEmail(),
                "status"=>$result->getStatusAsString(),
                "message"=>$result->getMessage()
            ];
        } else {
            $status = JsonResponse::HTTP_BAD_REQUEST;
            $data = $errorDecorator->decorateError($status, $result);
        }

        return new JsonResponse($data, $status);
    }
}