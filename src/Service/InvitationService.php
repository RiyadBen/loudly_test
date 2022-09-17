<?php


namespace App\Service;


use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use \Doctrine\Common\Collections\Criteria;

class InvitationService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Create an invite by given data
     *
     * @param $data array which contains information about invite
     *    $data = [
     *      'sender' => (int) Sender. Required
     *      'receiver' => (int) Receiver. Required.
     *      'message' => (string) Message to be sent with invite. Required.
     *      'status' => (string) Status. Optional.
     *    ]
     * @return Invitation|string Invitation or error message
     */
    public function createInvite(array $data)
    {
        $receiver = $data['receiver'];
        $sender = $data['sender'];
        $message = $data['message'];
        $status = $data["status"]?? null;

        if (!($receiver instanceof User) || !($sender instanceof User)) {
            return "Receiver and Sender Required";
        }

        try {
            $invite = new Invitation($sender, $receiver, $message);
            if ($status) {

                $invite->setStatus($status);
            }
            $this->em->persist($invite);
            $this->em->flush();

            return $invite;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @param $user
     * @return array|object[]
     */
    public function getSentInvites($user)
    {
        return $invites = $this->em->getRepository('App\Entity\Invitation')
            ->findBy(['sender' => $user]);
    }

    /**
     * @param $email
     * @return array|object[]
     */
    public function getMyInvites($user)
    {
        $expressionBuilder = Criteria::expr();

        $criteria = new Criteria();
        $criteria->where($expressionBuilder->neq('status', Invitation::STATUS_CANCELLED));
        $criteria->andWhere($expressionBuilder->eq('receiver', $user));

        return $invites = $this->em->getRepository('App\Entity\Invitation')->matching($criteria);
    }
    /**
     * @param Invitation $invite
     * @return bool|Invitation True if invite was found , error otherwise
     */
    public function getInvite(Invitation $invite,$user)
    {
        if($invite->getSender()->getEmail()!=$user && $invite->getReceiver()->getEmail()!=$user){
            return "Not Found";
        }

        return $invite;
    }
    /**
     * @param Invitation $invite
     * @return bool|Invitation True if invite status was successfully set, error message otherwise
     */
    public function setInvitationStatus(Invitation $invite, $status)
    {
        try {
            $invite->setStatus($status);
            $this->em->persist($invite);
            $this->em->flush();
        } catch (\Exception $ex) {
            return "Unable to set status";
        }

        return $invite;
    }

    /**
     * @param Invitation $invite
     * @param string $user
     * @return bool|Invitation True if invite status was successfully set, error message otherwise
     */
    public function acceptInvitation(Invitation $invite, $user)
    {
        if($invite->getReceiver()->getEmail() != $user)  return Invitation::RECEIVER_ACCEPT_MSG;

        if($invite->getStatus() == Invitation::STATUS_CANCELLED) return Invitation::ACCEPT_CANCELLED_MSG;

        return $this->setInvitationStatus($invite, Invitation::STATUS_ACCEPTED);
    }
    /**
     * @param Invitation $invite
     * @param string $user
     * @return bool|Invitation True if invite status was successfully set, error message otherwise
     */
    public function rejectInvitation(Invitation $invite, $user)
    {
        if($invite->getReceiver()->getEmail() != $user)  return Invitation::RECEIVER_REJECT_MSG;

        if($invite->getStatus() == Invitation::STATUS_CANCELLED) return Invitation::REJECT_CANCELLED_MSG;

        return $this->setInvitationStatus($invite, Invitation::STATUS_REJECTED);

    }
    /**
     * @param Invitation $invite
     * @param string $user
     * @return bool|Invitation True if invite status was successfully set, error message otherwise
     */
    public function cancelInvitation(Invitation $invite, $user)
    {
        if ($invite->getSender()->getEmail() != $user) return Invitation::SENDER_CANCEL_MSG;

        return $this->setInvitationStatus($invite, Invitation::STATUS_CANCELLED);

    }

}