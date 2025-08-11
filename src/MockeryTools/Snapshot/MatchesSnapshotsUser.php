<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Snapshot;

use Nette\StaticClass;

/**
 * The sole purpose of this class is to have usage of MatchesSnapshots trait in this package so it is analyzed by PhpStan
 *
 * @see https://phpstan.org/blog/how-phpstan-analyses-traits
 * @final
 */
class MatchesSnapshotsUser
{
    use MatchesSnapshots;
    use StaticClass;
}
