<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Mapping;

final class MetadataMapping
{
    /**
     * @var MetadataLocalizationMapping[]
     */
    public array $localizationMapping;
    /**
     * @var MetadataPropertyMapping[]
     */
    public array $propertyMapping;

    /**
     * @param MetadataLocalizationMapping[] $localizationMapping
     * @param MetadataPropertyMapping[] $propertyMapping
     */
    public function __construct(
        /** public readonly */
        array $localizationMapping,
        /** public readonly */
        array $propertyMapping
    ) {
        $this->localizationMapping = $localizationMapping;
        $this->propertyMapping = $propertyMapping;
    }
}
