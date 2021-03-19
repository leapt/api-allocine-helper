<?php

declare(strict_types=1);

namespace Leapt\ApiAllocineHelper\Model;

use Leapt\ApiAllocineHelper\Configuration;

final class Movie
{
    public int $code;
    public string $title;
    public string $originalTitle;
    public ?string $synopsis;
    public ?string $shortSynopsis;
    public Image $poster;
    public string $movieType;
    public string $keywords;
    public int $productionYear;
    public array $nationalities;
    public array $genres;
    public ?int $runtime;
    public array $shortCastingDirectors;
    public array $shortCastingActors;
    public ?string $trailerEmbed;
    public ?float $pressRating;
    public ?float $userRating;

    public function __construct(Configuration $config, public array $data)
    {
        $this->code = $data['code'];
        $this->originalTitle = $data['originalTitle'];
        $this->title = $data['title'] ?? $data['originalTitle'];
        $this->synopsis = $this->fixApostrophes($data['synopsis']);
        $this->shortSynopsis = $this->fixApostrophes($data['synopsisShort']);
        $this->poster = new Image($config, $data['poster']['href'] ?? null);
        $this->movieType = $data['movieType']['$'];
        $this->keywords = $data['keywords'];
        $this->productionYear = $data['productionYear'];
        $this->nationalities = array_map(fn (array $nationality) => $nationality['$'], $data['nationality']);
        $this->genres = array_map(fn (array $genre) => $genre['$'], $data['genre']);
        $this->runtime = $data['runtime'] ?? null;
        $this->shortCastingDirectors = explode(', ', $data['castingShort']['directors']);
        $this->shortCastingActors = explode(', ' , $data['castingShort']['actors']);
        $this->trailerEmbed = $data['trailerEmbed'] ?? null;
        $this->pressRating = $data['statistics']['pressRating'] ?? null;
        $this->userRating = $data['statistics']['userRating'] ?? null;
    }

    private function fixApostrophes(?string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        return preg_replace('#\p{L}\K[‘’](?=\p{L})#u', '\'', $data);
    }
}
