<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PaginationException;
use EDT\Querying\Contracts\SortException;
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
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template O of object
 * @template TWrapper
 *
 * @deprecated use the individual components manually and optimize them for your use-case
 */
class GenericEntityFetcher
{
    /**
     * @var ObjectProviderInterface<TCondition, TSorting, O>
     */
    private $objectProvider;
    /**
     * @var PathsBasedConditionFactoryInterface<TCondition>
     */
    private $conditionFactory;
    /**
     * @var WrapperFactoryInterface<TCondition, TSorting>
     */
    private $wrapperFactory;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param ObjectProviderInterface<TCondition, TSorting, O>                      $objectProvider
     * @param \EDT\ConditionFactory\PathsBasedConditionFactoryInterface<TCondition> $conditionFactory
     * @param WrapperFactoryInterface<TCondition, TSorting>                         $wrapperFactory All returned instances are wrapped using the given instance.
     *                                                             To avoid any wrapping simply pass an instance that returns
     *                                                             its input without wrapping.
     */
    public function __construct(
        ObjectProviderInterface $objectProvider,
        PathsBasedConditionFactoryInterface $conditionFactory,
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
     * @param ReadableTypeInterface<TCondition, TSorting, O> $type
     * @param list<TCondition>                        $conditions
     * @param TSorting                              ...$sortMethods
     *
     * @return list<TWrapper>
     *
     * @throws AccessException
     * @throws SortException
     * @throws PaginationException
     */
    public function listEntities(ReadableTypeInterface $type, array $conditions, PathsBasedInterface ...$sortMethods): array
    {
        $restrictedProvider = new TypeRestrictedEntityProvider(
            $this->objectProvider,
            $type,
            $this->schemaPathProcessor
        );

        // get and map the actual entities
        $entities = $restrictedProvider->getObjects($conditions, array_values($sortMethods));
        $entities = Iterables::asArray($entities);
        $entities = array_values($entities);

        return array_map(function (object $object) use ($type) {
            return $this->wrapperFactory->createWrapper($object, $type);
        }, $entities);
    }

    /**
     * @param IdentifiableTypeInterface<TCondition, TSorting, O>&ReadableTypeInterface<TCondition, TSorting, O> $type
     * @param non-empty-string $identifier
     *
     * @return TWrapper
     * @throws PaginationException
     * @throws SortException
     * @throws PathException
     * @throws AccessException
     */
    public function getEntityByIdentifier(IdentifiableTypeInterface $type, string $identifier)
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
