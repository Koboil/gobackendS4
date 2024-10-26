<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class LogSearchFilter implements FilterInterface
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function apply(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        ?Operation                  $operation = null,
        array                       $context = []
    ): void
    {
        if (!isset($context['filters']['search'])) {
            return;
        }

        $searchValue = $context['filters']['search'];
        $alias = $queryBuilder->getRootAliases()[0];

        $valueParameter = $queryNameGenerator->generateParameterName('search');

        $queryBuilder->andWhere(sprintf(
            '%s.IP LIKE :%s OR %s.userFirstName LIKE :%s OR %s.userLastName LIKE :%s OR %s.uri LIKE :%s OR %s.error LIKE :%s',
            $alias,
            $valueParameter,
            $alias,
            $valueParameter,
            $alias,
            $valueParameter,
            $alias,
            $valueParameter,
            $alias,
            $valueParameter
        ))
            ->setParameter($valueParameter, '%' . $searchValue . '%');
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'Search across multiple fields',
                ],
            ]
        ];
    }
}
