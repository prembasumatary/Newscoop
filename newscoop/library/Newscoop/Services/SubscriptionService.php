<?php
/**
 * @package Newscoop
 * @copyright 2011 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\Subscription\Subscription;
use Newscoop\Subscription\SubscriptionData;

/**
 */
class SubscriptionService
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;

    /**
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create()
    {
        $subscription = new Subscription();

        return $subscription;
    }

    public function save(Subscription $subscription) {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function remove($id)
    {

    }

    public function getOneById($id)
    {
        $subscription = $this->em->getRepository('Newscoop\Entity\Subscription')->findOneBy(array(
            'id' => $id
        ));

        return $subscription;
    }

    public function getOneByUserAndPublication($userId, $publicationId)
    {
        $subscription = $this->em->getRepository('Newscoop\Entity\Subscription')->findOneBy(array(
            'user' => $userId,
            'publication' => $publicationId
        ));

        return $subscription;
    }

    /**
     * Update Subscription according to SubscritionData class
     * @param  Subscription     $subscription
     * @param  SubscriptionData $data
     * @return Subscription
     */
    public function update(Subscription $subscription, SubscriptionData $data)
    {
        $subscription = $this->apply($subscription, $data);

        return $subscription;
    }

    private function apply(Subscription $subscription, SubscriptionData $data) 
    {
        if ($data->userId) {
            $user = $this->em->getRepository('Newscoop\Entity\User')->getOneActiveUser($data->userId, false)->getOneOrNullResult();
            if ($user) {
                $subscription->setUser($user);    
            }
        }

        if ($data->publicationId) {
            $publication = $this->em->getRepository('Newscoop\Entity\Publication')->findOneBy(array('id' => $data->publicationId));
            if ($publication) {
                $subscription->setPublication($publication);
            }
        }

        if ($data->toPay) {
            $subscription->setToPay($data->toPay);
        }

        if ($data->currency) {
            $subscription->setCurrency($data->currency);
        }

        if ($data->active) {
            $subscription->setActive($data->active);
        }

        if ($data->type) {
            $subscription->setType($data->type);
        }

        if ($data->sections) {
            $sectionsIds = array();
            foreach ($data->sections as $key => $section) {
                $subscription->addSection($section);
                $sectionsIds[] = $section->getId();
            }

            //Clean conncted sections list
            $subscription->setSections($sectionsIds);
        }

        if ($data->articles) {
            $articlesIds = array();
            foreach ($data->articles as $key => $article) {
                $subscription->addArticle($article);
                $articlesIds[] = $article->getId();
            }

            //Clean conncted sections list
            $subscription->setArticles($articlesIds);
        }
        
        return $subscription;
    }

    public function getArticleRepository(){
        return $this->em->getRepository('Newscoop\Entity\Article');
    }

    public function getSectionRepository(){
        return $this->em->getRepository('Newscoop\Entity\Section');
    }

    public function getLanguageRepository(){
        return $this->em->getRepository('Newscoop\Entity\Language');
    }
}
