<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2016, ecentria group, inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecentria\Libraries\EcentriaRestBundle\Services\Transaction\Storage;

use Ecentria\Libraries\EcentriaRestBundle\Entity\Transaction as TransactionEntity,
    Ecentria\Libraries\EcentriaRestBundle\Model\Transaction as TransactionModel,
    Ecentria\Libraries\EcentriaRestBundle\Converter\EntityConverter;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Persistence\ManagerRegistry,
    Doctrine\Common\Util\ClassUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

/**
 * Doctrine Transaction Storage
 *
 * @author Artem Petrov <artem.petrov@opticsplanet.com>
 */
class Doctrine implements TransactionStorageInterface {

    /**
     * Registry
     *
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * Request
     *
     * @var Request
     */
    private $request;

    /**
     * Entity Converter
     *
     * @var EntityConverter
     */
    private $entityConverter;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     * @param EntityConverter $entityConverter
     */
    public function __construct(ManagerRegistry $registry, EntityConverter $entityConverter) {
        $this->registry = $registry;
        $this->entityConverter = $entityConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function write(TransactionModel $transaction) {
        $entity = $this->buildEntity($transaction);
        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($entity) : get_class($entity);
        $entityManager = $this->registry->getManagerForClass($className);

        $entityManager->clear();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function read($id) {
        $values = [
            'class'     => 'Ecentria\Libraries\EcentriaRestBundle\Entity\Transaction',
            'converter' => 'ecentria.api.converter.entity',
            'name'      => 'transactionEntity'
        ];

        $configuration = new ParamConverter($values);
        $this->entityConverter->apply($this->request, $configuration);
        $transactionEntity = $this->request->attributes->get('transactionEntity');

        return $this->buildModel($transactionEntity);
    }

    /**
     * Set request
     *
     * @param Request $request Request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Build Transaction Entity from Transaction Model
     *
     * @param TransactionModel $transactionModel Transaction Model
     * @return TransactionEntity
     */
    private function buildEntity(TransactionModel $transactionModel)
    {
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setId($transactionModel->getId())
            ->setModel($transactionModel->getModel())
            ->setRelatedIds($transactionModel->getRelatedIds())
            ->setRelatedRoute($transactionModel->getRelatedRoute())
            ->setMethod($transactionModel->getMethod())
            ->setRequestSource($transactionModel->getRequestSource())
            ->setRequestId($transactionModel->getRequestId())
            ->setCreatedAt($transactionModel->getCreatedAt())
            ->setUpdatedAt($transactionModel->getUpdatedAt())
            ->setStatus($transactionModel->getStatus())
            ->setSuccess($transactionModel->getSuccess())
            ->setMessages(new ArrayCollection($transactionModel->getMessages()));

        return $transactionEntity;
    }

    /**
     * Build Transaction Model from Transaction Entity
     *
     * @param TransactionEntity $transactionEntity
     * @return TransactionModel
     */
    private function buildModel(TransactionEntity $transactionEntity)
    {
        $transactionModel = new TransactionModel();
        $transactionModel->setId($transactionEntity->getId())
            ->setModel($transactionEntity->getModel())
            ->setRelatedIds($transactionEntity->getRelatedIds())
            ->setRelatedRoute($transactionEntity->getRelatedRoute())
            ->setMethod($transactionEntity->getMethod())
            ->setRequestSource($transactionEntity->getRequestSource())
            ->setRequestId($transactionEntity->getRequestId())
            ->setCreatedAt($transactionEntity->getCreatedAt())
            ->setUpdatedAt($transactionEntity->getUpdatedAt())
            ->setStatus($transactionEntity->getStatus())
            ->setSuccess($transactionEntity->getSuccess())
            ->setMessages($transactionEntity->getMessages()->toArray());

        return $transactionModel;
    }
}
