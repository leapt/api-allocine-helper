<?php

declare(strict_types=1);

namespace Leapt\ApiAllocineHelper\Tests;

use Leapt\ApiAllocineHelper\Exception\NoResultsException;
use Leapt\ApiAllocineHelper\Helper;
use PHPUnit\Framework\TestCase;

final class HelperTest extends TestCase
{
    public function testMovie(): void
    {
        $helper = new Helper();
        $movie = $helper->movie(29276);
        self::assertSame(29276, $movie->code);
        self::assertSame('Harry Potter à l\'école des sorciers', $movie->title);
        self::assertSame('Harry Potter and the Philosopher\'s Stone', $movie->originalTitle);
        self::assertSame('Long-métrage', $movie->movieType);
        self::assertSame(2001, $movie->productionYear);
        self::assertSame(['U.S.A.', 'Grande-Bretagne'], $movie->nationalities);
        self::assertSame(['Fantastique', 'Aventure', 'Famille'], $movie->genres);
        self::assertSame(9120, $movie->runtime);
        self::assertSame(['Chris Columbus'], $movie->shortCastingDirectors);
        self::assertSame(['Daniel Radcliffe', 'Rupert Grint', 'Emma Watson', 'Robbie Coltrane', 'Richard Harris'], $movie->shortCastingActors);

        $this->expectException(NoResultsException::class);
        $helper->movie(11111111111);
    }
}
