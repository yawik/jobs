<?php
/**
 * YAWIK
 *
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Jobs\Repository;

use Auth\Entity\UserInterface;
use Core\Repository\AbstractRepository;
use Core\Repository\DoctrineMongoODM\PaginatorAdapter;
use Doctrine\ODM\MongoDB\Cursor;
use Jobs\Entity\StatusInterface;

/**
 * Class Job
 *
 */
class Job extends AbstractRepository
{
    /**
     * Gets a pagination cursor to the jobs collection
     *
     * @param $params
     * @return mixed
     */
    public function getPaginatorCursor($params)
    {
        $filter = $this->getService('filterManager')->get('Jobs/PaginationQuery');
        /* @var $filter \Core\Repository\Filter\AbstractPaginationQuery  */
        $qb = $filter->filter($params, $this->createQueryBuilder());
        return $qb->getQuery()->execute();
    }

    /**
     * Checks, if a job posting with a certain applyId (external job id) exists
     *
     * @param $applyId
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function existsApplyId($applyId)
    {
        $qb = $this->createQueryBuilder();
        $qb->hydrate(false)
           ->select('applyId')
           ->field('applyId')->equals($applyId);
           
        $result = $qb->getQuery()->execute();
        $count = $result->count();
        return (bool) $count;
    }

    /**
     * @param $resourceId
     * @return array
     */
    public function findByAssignedPermissionsResourceId($resourceId)
    {
        return $this->findBy(
            array(
            'permissions.assigned.' . $resourceId => array(
                '$exists' => true
            )
            )
        );
    }

    /**
     * Gets the Job Titles of a certain user.
     *
     * @param $query
     * @param $userId
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getTypeAheadResults($query, $userId)
    {
        $qb = $this->createQueryBuilder();
        $qb->hydrate(false)
           ->select('title', 'applyId')
           ->field('permissions.view')->equals($userId)
           ->field('title')->equals(new \MongoRegex('/' . $query . '/i'))
           ->sort('title')
           ->limit(5);
        
        $result = $qb->getQuery()->execute();
        
        return $result;
    }

    /**
     * Look for an drafted Document of a given user
     *
     * @param $user
     * @return \Jobs\Entity\Job|null
     */
    public function findDraft($user)
    {
        if ($user instanceof UserInterface) {
            $user = $user->getId();
        }

        $document = $this->findOneBy(
            array(
            'isDraft' => true,
            'user' => $user
            )
        );

        if (!empty($document)) {
            return $document;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUniqueReference()
    {
        return uniqid();
    }

    /**
     * Selects job postings of a certain organization
     *
     * @param int $organizationId
     * @return \Jobs\Entity\Job[]
     */
    public function findByOrganization($organizationId)
    {
        return $this->findBy([
            'organization' => new \MongoId($organizationId)
        ]);
    }

    /**
     * Selects all Organizations with Active Jobs
     *
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findActiveOrganizations()
    {
        $qb = $this->createQueryBuilder();
        $qb->distinct('organization')
            ->hydrate(true)
           ->field('status.name')->notIn([ StatusInterface::EXPIRED, StatusInterface::INACTIVE ]);
        $q = $qb->getQuery();
        $r = $q->execute();
        $r = $r->toArray();

        $qb = $this->dm->createQueryBuilder('Organizations\Entity\Organization');
        $qb->field('_id')->in($r);
        $q = $qb->getQuery();
        $r = $q->execute();

        return $r;
    }

    /**
     * @return  Cursor
     * @throws  \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findActiveJob($hydrate = true)
    {
        $qb = $this->createQueryBuilder()
            ->hydrate($hydrate)
            ->refresh()
            ->field('status.name')->in([StatusInterface::ACTIVE])
            ->field('isDraft')->equals(false)
        ;
        $q  = $qb->getQuery();
        $r  = $q->execute();

        return $r;
    }
}
