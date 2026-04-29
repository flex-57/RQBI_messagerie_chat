<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationMember;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConversationMember>
 */
class ConversationMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMember::class);
    }

    public function findMember(Conversation $conversation, User $user): ?ConversationMember
    {
        return $this->findOneBy([
            'conversation' => $conversation,
            'user' => $user,
        ]);
    }

    public function findMembersByConversation(Conversation $conversation)
    {
        return $this->findBy(['conversation' => $conversation]);
    }
}
