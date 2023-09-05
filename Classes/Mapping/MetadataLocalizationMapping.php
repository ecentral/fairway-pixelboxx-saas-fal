<?php

declare(strict_types=1);

/*
 * This file is part of the "pixelboxx_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\PixelboxxSaasFal\Mapping;

final class MetadataLocalizationMapping
{
    public int $languageId;
    public string $languageCode;

    public function __construct(
        /** public readonly */
        int $languageId,
        /** public readonly */
        string $languageCode
    ) {
        $this->languageId = $languageId;
        $this->languageCode = $languageCode;
    }
}
