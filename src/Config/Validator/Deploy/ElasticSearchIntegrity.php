<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Config\Validator\Deploy;

use Magento\MagentoCloud\App\Error;
use Magento\MagentoCloud\Config\Validator;
use Magento\MagentoCloud\Config\ValidatorException;
use Magento\MagentoCloud\Config\ValidatorInterface;
use Magento\MagentoCloud\Filesystem\FileSystemException;
use Magento\MagentoCloud\Package\MagentoVersion;
use Magento\MagentoCloud\Package\UndefinedPackageException;
use Magento\MagentoCloud\Service\ElasticSearch;
use Magento\MagentoCloud\Service\OpenSearch;

/**
 * Verifies if Elasticsearch service present for Magento 2.4.0 and above
 */
class ElasticSearchIntegrity implements ValidatorInterface
{
    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var Validator\ResultFactory
     */
    private $resultFactory;

    /**
     * @var ElasticSearch
     */
    private $elasticsearch;

    /**
     * @var OpenSearch
     */
    private $openSearch;

    /**
     * @param MagentoVersion $magentoVersion
     * @param Validator\ResultFactory $resultFactory
     * @param ElasticSearch $elasticSearch
     * @param OpenSearch $openSearch
     */
    public function __construct(
        MagentoVersion $magentoVersion,
        Validator\ResultFactory $resultFactory,
        ElasticSearch $elasticSearch,
        OpenSearch $openSearch
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->resultFactory = $resultFactory;
        $this->elasticsearch = $elasticSearch;
        $this->openSearch = $openSearch;
    }

    /**
     * @inheritDoc
     */
    public function validate(): Validator\ResultInterface
    {
        try {
            if ($this->magentoVersion->isGreaterOrEqual('2.4.4') && $this->openSearch->isInstalled()) {
                return $this->resultFactory->success();
            }

            if ($this->magentoVersion->isGreaterOrEqual('2.4.0')
                && !$this->elasticsearch->isInstalled()
            ) {
                return $this->resultFactory->errorByCode(Error::DEPLOY_ES_SERVICE_NOT_INSTALLED);
            }
        } catch (UndefinedPackageException | FileSystemException $exception) {
            throw new ValidatorException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->resultFactory->success();
    }
}
