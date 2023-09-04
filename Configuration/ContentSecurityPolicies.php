<?php
declare(strict_types=1);

use Fairway\PixelboxxSaasFal\Service\DomainConfigurationService;
use Fairway\PixelboxxSaasFal\Service\DomainConfigurationServiceFactory;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Type\Map;
use TYPO3\CMS\Core\Utility\GeneralUtility;

try {
    /** @var  DomainConfigurationService $service */
    $service = GeneralUtility::makeInstance(DomainConfigurationServiceFactory::class)();
    $collection = $service->getMutationCollection();
    $collection = $collection ?? new MutationCollection();

    return Map::fromEntries([Scope::backend(), $collection]);

} catch (Throwable) {

}

