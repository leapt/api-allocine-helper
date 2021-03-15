<?php

declare(strict_types=1);

namespace Leapt\ApiAllocineHelper\Tests\Model;

use Leapt\ApiAllocineHelper\Configuration;
use Leapt\ApiAllocineHelper\Model\Image;
use PHPUnit\Framework\TestCase;

final class PosterTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        $this->image = new Image(new Configuration(), 'http://fr.web.img1.acsta.net/medias/nmedia/18/62/87/33/18656571.jpg');
    }

    public function testInitialization(): void
    {
        self::assertSame('https://fr.web.img1.acsta.net/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testBorder(): void
    {
        $this->image->border(3, 'AEAEAE');
        self::assertSame('https://fr.web.img1.acsta.net/b_3_AEAEAE/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testCut(): void
    {
        $this->image->cut(100, 150);
        self::assertSame('https://fr.web.img1.acsta.net/c_100_150/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testResize(): void
    {
        $this->image->resize(200, 400);
        self::assertSame('https://fr.web.img1.acsta.net/r_200_400/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testIcon(): void
    {
        $this->image->icon('c', 4);
        self::assertSame('https://fr.web.img1.acsta.net/o_play.png_4_c/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testCombination(): void
    {
        $this->image->border(4, 'FF0000')
            ->resize(100, 150);
        self::assertSame('https://fr.web.img1.acsta.net/r_100_150/b_4_FF0000/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }

    public function testReset(): void
    {
        $this->image->resize(200, 400);
        self::assertSame('https://fr.web.img1.acsta.net/r_200_400/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
        $this->image->reset();
        self::assertSame('https://fr.web.img1.acsta.net/medias/nmedia/18/62/87/33/18656571.jpg', $this->image->getUrl());
    }
}
