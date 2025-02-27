<?php


namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Metadata\Operation;

class ReservationSearchFilter implements FilterInterface
{
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!isset($context['filters']['search'])) {
            return;
        }

        $searchValue = $context['filters']['search'];
        $alias = $queryBuilder->getRootAliases()[0];
        $valueParameter = $queryNameGenerator->generateParameterName('search');

        $queryBuilder->andWhere(sprintf('%s.startAt LIKE :%s  OR %s.endAt LIKE :%s', $alias, $valueParameter, $alias, $valueParameter))
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
