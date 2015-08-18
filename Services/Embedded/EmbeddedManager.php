<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2015, ecentria group, inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecentria\Libraries\EcentriaRestBundle\Services\Embedded;

use Symfony\Component\HttpFoundation\Request;

/**
 * Embedded manager
 *
 * @author Sergey Chernecov <sergey.chernecov@intexsys.lv>
 */
class EmbeddedManager
{
    const PROPERTY_DIVIDER = ',';
    const PROPERTY_COMBINER = '.';

    const PREFIX = 'embedded';

    const GROUP_DEFAULT = 'Default';
    const GROUP_ALL = 'all';
    const GROUP_VIOLATION_ENTITY = 'violation.entity';
    const GROUP_VIOLATION_COLLECTION = 'violation.collection';

    const KEY_EMBED = '_embed';
    const KEY_EMBEDDED = '_embedded';

    /**
     * Generating serialization embedded groups
     *
     * @param Request $request
     *
     * @return array
     */
    public static function generateGroups(Request $request)
    {
        $embed = $request->get(self::KEY_EMBED);

        $embedResult = [self::GROUP_DEFAULT];

        if (!is_null($embed)) {
            $embedAsArray = explode(self::PROPERTY_DIVIDER, $embed);
            array_walk(
                $embedAsArray,
                function (&$value) {
                    $value = self::generateGroupName($value);
                }
            );
            $embedResult = array_merge($embedResult, $embedAsArray);
        }

        $embedded = filter_var(
            $request->get(self::KEY_EMBEDDED),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($embedded) {
            $embedResult[] = self::generateGroupName(self::GROUP_ALL);
        }

        return $embedResult;
    }

    /**
     * Generate group name
     *
     * @param string $value
     *
     * @return string
     */
    public static function generateGroupName($value)
    {
        return self::PREFIX . self::PROPERTY_COMBINER . $value;
    }
}
