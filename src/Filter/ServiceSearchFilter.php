<?php


namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class ServiceSearchFilter implements FilterInterface
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

        $queryBuilder->innerJoin(sprintf('%s.company', $alias), 'company')->andWhere(sprintf('%s.name LIKE :%s OR %s.description LIKE :%s OR company.name LIKE :%s', $alias, $valueParameter, $alias, $valueParameter,$valueParameter))
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
