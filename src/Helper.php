<?php

declare(strict_types=1);

namespace Leapt\ApiAllocineHelper;

use Leapt\ApiAllocineHelper\Exception\ApiException;
use Leapt\ApiAllocineHelper\Exception\EmptyDataException;
use Leapt\ApiAllocineHelper\Exception\NoResultsException;
use Leapt\ApiAllocineHelper\Model\Movie;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Helper
{
    private HttpClientInterface $client;
    private array $preset;
    private Configuration $config;

    public function __construct(?Configuration $config = null)
    {
        $this->client = HttpClient::create();
        $this->config = $config ?? new Configuration();
        $this->preset = [
            'format'  => 'json',
            'partner' => $this->config->partnerCode,
        ];
    }

    public function movie(int $code, string $profile = 'medium', string &$url = null): Movie
    {
        $this->setProfile($profile);
        $this->setAllocineCode($code);
        $url = $this->createURL('rest/v3/movie');
        $data = $this->getDataFromUrl($url);

        return new Movie($this->config, $data['movie']);
    }

    private function getDataFromUrl(string $url): array
    {
        $userAgent = self::getRandomUserAgent();
        $ipAddress = self::getRandomIpAddress();

        $data = $this->client->request('GET', $url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => $userAgent,
                'REMOTE_ADDR' => $ipAddress,
                'HTTP_X_FORWARDED_FOR' => $ipAddress,
            ],
        ])->toArray();

        if (isset($data['error']['$'])) {
            throw match ($data['error']['$']) {
                'No result' => new NoResultsException(),
                default => new ApiException($data['error']['$']),
            };
        }

        if (empty($data)) {
            throw new EmptyDataException();
        }

        return $data;
    }

    public static function getRandomIpAddress(): string
    {
        return sprintf(
            '%s.%s.%s.%s',
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255),
            random_int(0, 255),
        );
    }

    public static function getRandomUserAgent(): string
    {
        $v = random_int(1, 4) . '.' . random_int(0, 9);
        $a = random_int(0, 9);
        $b = random_int(0, 99);
        $c = random_int(0, 999);

        $userAgents = [
            "Mozilla/5.0 (Linux; U; Android $v; fr-fr; Nexus One Build/FRF91) AppleWebKit/5$b.$c (KHTML, like Gecko) Version/$a.$a Mobile Safari/5$b.$c",
            "Mozilla/5.0 (Linux; U; Android $v; fr-fr; Dell Streak Build/Donut AppleWebKit/5$b.$c+ (KHTML, like Gecko) Version/3.$a.2 Mobile Safari/ 5$b.$c.1",
            "Mozilla/5.0 (Linux; U; Android 4.$v; fr-fr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30",
            "Mozilla/5.0 (Linux; U; Android 4.$v; fr-fr; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30",
            "Mozilla/5.0 (Linux; U; Android $v; en-gb) AppleWebKit/999+ (KHTML, like Gecko) Safari/9$b.$a",
            "Mozilla/5.0 (Linux; U; Android $v.5; fr-fr; HTC_IncredibleS_S710e Build/GRJ$b) AppleWebKit/5$b.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Vision Build/GRI$b) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android $v.4; fr-fr; HTC Desire Build/GRJ$b) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; T-Mobile myTouch 3G Slide Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android $v.3; fr-fr; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; LG-LU3000 Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/5$b.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_DesireS_S510e Build/GRI$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC_DesireS_S510e Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile",
            "Mozilla/5.0 (Linux; U; Android $v.3; fr-fr; HTC Desire Build/GRI$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android 2.$v; fr-fr; HTC Desire Build/FRF$a) AppleWebKit/533.1 (KHTML, like Gecko) Version/$a.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android $v; fr-lu; HTC Legend Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/$a.$a Mobile Safari/$c.$a",
            "Mozilla/5.0 (Linux; U; Android $v; fr-fr; HTC_DesireHD_A9191 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Mozilla/5.0 (Linux; U; Android $v.1; fr-fr; HTC_DesireZ_A7$c Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.$a",
            "Mozilla/5.0 (Linux; U; Android $v.1; en-gb; HTC_DesireZ_A7272 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/$c.1",
            "Mozilla/5.0 (Linux; U; Android $v; fr-fr; LG-P5$b Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"
        ];

        return $userAgents[random_int(0, count($userAgents) - 1)];
    }

    private function createURL(string $endpoint): string
    {
        $params = $this->preset;
        $params['filter'] = isset($params['filter']) ? implode(',', $params['filter']) : null;
        $params['sed'] = date('Ymd');
        $params['sig'] = base64_encode(sha1(basename($endpoint) . http_build_query($params) . $this->config->apiSecret, true));

        return $this->config->apiUrl . '/' . $endpoint . '?' . http_build_query($params);
    }

    private function setProfile(string $profile): self
    {
        $allowedProfiles = ['small', 'medium', 'large'];

        if (!\in_array($profile, $allowedProfiles, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid profile "%s", expected one of "%s".', $profile, implode('", "', $allowedProfiles)));
        }

        $this->preset['profile'] = $profile;

        return $this;
    }

    private function setAllocineCode(int $code): self
    {
        $this->preset['code'] = $code;

        return $this;
    }
}
