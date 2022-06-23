<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\SliceException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\ObjectProviders\TypeRestrictedEntityProvider;
use EDT\Querying\Utilities\Iterables;
use EDT\Querying\Contracts\ObjectProviderInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use function count;

/**
 * @template O of object
 * @template R
 */
class GenericEntityFetcher
{
    /**
     * @var ObjectProviderInterface<O>
     */
    private $objectProvider;
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;
    /**
     * @var WrapperFactoryInterface<O,R>
     */
    private $wrapperFactory;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param ObjectProviderInterface<O> $objectProvider
     * @param WrapperFactoryInterface<O,R> $wrapperFactory All returned instances are wrapped using the given instance.
     *                                                             To avoid any wrapping simply pass an instance that returns
     *                                                             its input without wrapping.
     */
    public function __construct(
        ObjectProviderInterface $objectProvider,
        ConditionFactoryInterface $conditionFactory,
        SchemaPathProcessor $schemaPathProcessor,
        WrapperFactoryInterface $wrapperFactory
    ) {
        $this->objectProvider = $objectProvider;
        $this->conditionFactory = $conditionFactory;
        $this->wrapperFactory = $wrapperFactory;
        $this->schemaPathProcessor = $schemaPathProcessor;
    }

    /**
     * Will return all entities matching the given condition with the specified sorting wrapped into instances created
     * using {@link GenericEntityFetcher::$wrapperFactory}.
     *
     * For all properties accessed while filtering/sorting it is checked if:
     *
     * * the given type and the types in the property paths are {@link TypeInterface::isAvailable() available at all} and {@link ReadableTypeInterface readable}
     * * the property is available for {@link FilterableTypeInterface::getFilterableProperties() filtering} if conditions were given
     * * the property is available for {@link SortableTypeInterface::getSortableProperties() sorting} if sort methods were given
     *
     * @param ReadableTypeInterface<O> $type
     * @param array<int,FunctionInterface<bool>> $conditions
     * @return array<int,R>
     *
     * @throws AccessException
     * @throws SortException
     * @throws SliceException
     */
    public function listEntities(ReadableTypeInterface $type, array $conditions, SortMethodInterface ...$sortMethods): array
    {
        $restrictedProvider = new TypeRestrictedEntityProvider(
            $this->objectProvider,
            $type,
            $this->schemaPathProcessor
        );

        // get and map the actual entities
        $entities = $restrictedProvider->getObjects($conditions, $sortMethods);
        $entities = Iterables::asArray($entities);

        return array_map(function ($object) use ($type) {
            return $this->wrapperFactory->createWrapper($object, $type);
        }, $entities);
    }

    /**
     * @param IdentifiableTypeInterface<O>&ReadableTypeInterface<O> $type
     * @param mixed $identifier
     * @return R
     * @throws SliceException
     * @throws SortException
     * @throws PathException
     * @throws AccessException
     */
    public function getEntityByIdentifier(IdentifiableTypeInterface $type, $identifier)
    {
        $identifierPath = $type->getIdentifierPropertyPath();
        $identifierCondition = $this->conditionFactory->propertyHasValue($identifier, ...$identifierPath);
        $entities = $this->listEntities($type, [$identifierCondition]);

        switch (count($entities)) {
            case 0:
                throw AccessException::noEntityByIdentifier($type);
            case 1:
                return array_pop($entities);
            default:
                throw AccessException::multipleEntitiesByIdentifier($type);
        }
    }
}
