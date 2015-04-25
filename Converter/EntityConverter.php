<?php
/*
 * This file is part of the Ecentria software.
 *
 * (c) 2015, Ecentria, Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecentria\Libraries\CoreRestBundle\Converter;

use Ecentria\Libraries\CoreRestBundle\Model\Alias;
use Ecentria\Libraries\CoreRestBundle\Model\CRUD\CrudEntityInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as BaseDoctrineParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Modified DoctrineParamConverter.
 *
 * @author Sergey Chernecov <sergey.chernecov@intexsys.lv>
 */
class EntityConverter extends BaseDoctrineParamConverter
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name    = $configuration->getName();
        $class   = $configuration->getClass();
        $options = $this->getOptions($configuration);

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        // find by identifier?
        if (null === $object = $this->find($class, $request, $options, $name)) {
            // find by criteria
            if (null === $object = $this->findOneBy($class, $request, $options)) {
                $object = new $class();
                if ($object instanceof CrudEntityInterface) {
                    $object->setId($request->attributes->get('id'));
                }
            }
        }

        $request->attributes->set($name, $object);

        /**
         * Alias to access current collection
         * Used by exception listener
         */
        $request->attributes->set(Alias::DATA, $name);

        return true;
    }
}
