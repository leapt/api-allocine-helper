<?php

declare(strict_types=1);

namespace Leapt\ApiAllocineHelper\Model;

use Leapt\ApiAllocineHelper\Configuration;

final class Image
{
    private string $imageHost;
    private string $imagePath;
    private ?array $imageSize = null;
    private ?array $imageBorder = null;
    private ?array $imageIcon = null;
    private array $icons = [
        'play.png' => null,
        'overplay.png' => null,
        'overlayVod120.png' => ['r', 120, 160],
    ];

    public function __construct(Configuration $config, ?string $url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->imageHost = $config->defaultUrlImages;
            $this->imagePath = $config->defaultImagePath;
        } else {
            $urlParse = parse_url($url);

            $this->imageHost = !empty($urlParse['host']) ? $urlParse['host'] : $config->defaultUrlImages;

            if (!empty($urlParse['path'])) {
                $this->imagePath = $urlParse['path'];
            }
            else {
                throw new \InvalidArgumentException(sprintf('Invalid image URL "%s".', $urlParse['path']));
            }

            // Parsage de l'URL
            $explodePath = explode('/', $this->imagePath);

            // Première partie vide ?
            if (empty($explodePath[0])) {
                unset($explodePath[0]);
            }

            // Détecte les paramètres jusqu'au début du path réel.
            foreach ($explodePath as $iPathPart => $pathPart)
            {
                if (!str_contains($pathPart, '_')) {
                    break;
                }

                unset($explodePath[$iPathPart]);

                // Icône
                if (str_starts_with($pathPart, 'o') && preg_match("#^o_(.+)_(.+)_(.+)$#i", $pathPart, $i) !== false) {
                    $this->icon($i[3], $i[2], $i[1]);
                }

                // Bordure
                elseif (str_starts_with($pathPart, 'b') && preg_match("#^b[xy]?_([0-9]+)_([0-9a-f]{6}|.*)$#i", $pathPart, $i) !== false) {
                    if (preg_match("#^[0-9a-f]{6}$#i", $i[2]) === false) {
                        $i[2] = "000000";
                    }

                    $this->border($i[1], $i[2]);
                }

                // Redimensionnement
                elseif (preg_match("#^r[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)$#i", $pathPart, $i) !== false) {
                    $this->resize((int) $i[1], (int) $i[2]);
                }

                // Recoupe
                elseif (preg_match("#^c[xy]?_([0-9]+|[a-z0-9]+)_([0-9]+|[a-z0-9]+)$#i", $pathPart, $i) !== false) {
                    $this->cut((int) $i[1], (int) $i[2]);
                }
            }

            $this->imagePath = implode('/', $explodePath);
        }
    }

    public function getUrl(): string
    {
        $params = [];

        // Taille
        if (null !== $this->imageSize) {
            $params[] = "{$this->imageSize['method']}_{$this->imageSize['xmax']}_{$this->imageSize['ymax']}";
        }

        // Bordure
        if (null !== $this->imageBorder) {
            $params[] = "b_{$this->imageBorder['size']}_{$this->imageBorder['color']}";
        }

        // Icône
        if (null !== $this->imageIcon) {
            $params[] = "o_{$this->imageIcon['icon']}_{$this->imageIcon['margin']}_{$this->imageIcon['position']}";
        }

        return "https://{$this->imageHost}" . (!empty($params) ? '/' . implode('/', $params) : '') . "/{$this->imagePath}";
    }

    /**
     * Modifier l'icône sur l'image.
     *
     * @param string $position='c' La position de l'icône par rapport au centre de l'image (en une ou deux lettres), d'après la rose des sable. Renseigner une position invalide (telle que 'c') pour centrer l'icône.
     * @param int $margin=4 Le nombre de pixel entre l'icône et le(s) bord(s) le(s) plus proche(s).
     * @param string $icon='play.png' Le nom de l'icône à ajouter. La liste des icônes se trouve dans AlloImage::$icons.
     */
    public function icon(string $position = 'c', int $margin = 4, string $icon = 'play.png'): self
    {
        if (!empty($this->icons[$icon])) {
            $p = $this->icons[$icon];

            switch ($p[0]) {
                case 'r':
                    $this->resize($p[1], $p[2]);
                    break;
                case 'c':
                    $this->cut($p[1], $p[2]);
                    break;
            }
        }

        $this->imageIcon = [
            'position' => substr($position, 0, 2),
            'margin' => $margin,
            'icon' => $icon,
        ];

        return $this;
    }

    /**
     * Modifier la bordure de l'image.
     *
     * @param int $size=1 L'épaisseur de la bordure en pixels.
     * @param string $color='000000' La couleur de la bordure en hexadécimal (sans # initial). [http://en.wikipedia.org/wiki/Web_colors#Hex_triplet]
     */
    public function border(int $size=1, string $color= '000000'): self
    {
        $this->imageBorder = [
            'size' => $size,
            'color' => $color,
        ];

        return $this;
    }

    /**
     * Modifier proportionnellement la taille de l'image au plus petit.
     * Si les deux paramètres sont laissés tels quels ($xmax='x' et $ymax='y'), l'image sera de taille normale.
     * Appeler cette fonction efface les paramètres enregistrés pour AlloImage::cut() (Les deux méthodes ne peuvent être utilisées en même temps).
     *
     * @param int $xmax='x' La largeur maximale de l'image, en pixels. Laisser 'x' pour une largeur automatique en fonction de $ymax.
     * @param int $ymax='y' La hauteur maximale de l'image, en pixels. Laisser 'y' pour une hauteur automatique en fonction de $xmax.
     */
    public function resize($xmax='x', $ymax='y'): self
    {
        $this->imageSize = [
            'method' => 'r',
            'xmax' => $xmax,
            'ymax' => $ymax,
        ];

        return $this;
    }


    /**
     * Redimensionner l'image au plus petit, puis couper les bords trop grands.
     * Appeler cette fonction efface les paramètres enregistrés pour AlloImage::resize() (Les deux méthodes ne peuvent être utilisées en même temps).
     *
     * @param int $xmax La largeur maximale de l'image, en pixels.
     * @param int $ymax La hauteur maximale de l'image, en pixels.
     */
    public function cut(int $xmax, int $ymax): self
    {
        $this->imageSize = [
            'method' => 'c',
            'xmax' => $xmax,
            'ymax' => $ymax,
        ];

        return $this;
    }

    public function reset(): self
    {
        $this->imageSize = $this->imageBorder = $this->imageIcon = null;

        return $this;
    }
}
