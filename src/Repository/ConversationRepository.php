<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('c')
            ->join('c.members', 'm')
            ->where('m.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPrivateConversation(User $user1, User $user2): ?Conversation
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.type = :type')
            ->setParameter('type', Conversation::TYPE_PRIVATE);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->isMemberOf(':user1', 'c.members'),
                    $qb->expr()->isMemberOf(':user2', 'c.members')
                )
            )
        )
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
