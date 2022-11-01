<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Mapping;

final class MetadataPropertyMapping
{
    public string $identifier;
    public string $property;
    /**
     * @var string[]
     */
    public array $translateToLanguages;
    public bool $enableBiDirectionalSyncing;

    /**
     * @param string $identifier
     * @param string $property
     * @param string[] $translateToLanguages
     * @param bool $enableBiDirectionalSyncing
     */
    public function __construct(
        string $identifier,
        string $property,
        array $translateToLanguages,
        bool $enableBiDirectionalSyncing = false
    ) {
        $this->identifier = $identifier;
        $this->property = $property;
        $this->translateToLanguages = $translateToLanguages;
        $this->enableBiDirectionalSyncing = $enableBiDirectionalSyncing;
    }
}
