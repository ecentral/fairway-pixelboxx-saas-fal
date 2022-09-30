<?php

/*
 * This file is part of the "canto_saas_fal" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF['canto_saas_fal'] = [
    'title' => 'Pixelboxx SaaS FAL',
    'description' => 'Adds Pixelboxx SaaS FAL driver.',
    'category' => 'misc',
    'version' => '0.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
            'filemetadata' => '10.4.0-11.9.99',
        ],
    ],
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'author' => 'Christian Rodriguez Benthake',
    'author_email' => 'c.rodriguez.benthake@ecentral.de',
    'author_company' => 'eCentral GmbH',
];
