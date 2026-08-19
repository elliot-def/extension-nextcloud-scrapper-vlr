<?php
namespace OCA\VlrCalendar\Service;

use OCP\Http\Client\IClientService;

class VlrScraper {
    private $clientService;
    private const VLR_BASE = 'https://www.vlr.gg';

    public function __construct(IClientService $clientService) {
        $this->clientService = $clientService;
    }

    private function getHtml(string $url): string {
        $client = $this->clientService->newClient();
        $response = $client->get($url);
        return $response->getBody();
    }

    public function resolvePlayerTeam(string $playerUrl): ?string {
        try {
            $html = $this->getHtml(self::VLR_BASE . $playerUrl);
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            $teamNodes = $xpath->query('//a[contains(@href, "/team/")]');
            
            foreach ($teamNodes as $node) {
                $text = $node->textContent;
                if (str_contains($text, 'joined') && !str_contains(strtolower($text), 'inactive')) {
                    $nameDivs = $xpath->query('.//div[@style="font-weight: 500;"]', $node);
                    if ($nameDivs->length > 0) {
                        return trim($nameDivs->item(0)->textContent);
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    public function getMatchesForTeams(array $targetTeams): array {
        $html = $this->getHtml(self::VLR_BASE . '/matches');
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $matches = [];
        $matchNodes = $xpath->query('//a[contains(@class, "match-item")]');

        $targetTeamsNormalized = array_map('strtolower', array_map('trim', $targetTeams));

        foreach ($matchNodes as $node) {
            $teams = $xpath->query('.//div[contains(@class, "match-item-vs-team-name")]', $node);
            if ($teams->length < 2) continue;

            $team1 = trim($teams->item(0)->textContent);
            $team2 = trim($teams->item(1)->textContent);

            $t1_norm = strtolower($team1);
            $t2_norm = strtolower($team2);

            $matchFound = false;
            foreach ($targetTeamsNormalized as $target) {
                if (str_contains($t1_norm, $target) || str_contains($t2_norm, $target)) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound) continue;

            $timeNode = $xpath->query('.//div[contains(@class, "match-item-time")]', $node);
            $timeStr = $timeNode->length > 0 ? trim($timeNode->item(0)->textContent) : '00:00';

            $dateQuery = $xpath->query('(./preceding::div[contains(@class, "wf-label")])[last()]', $node);
            $dateStr = $dateQuery->length > 0 ? trim($dateQuery->item(0)->textContent) : '';

            $fullDateTimeStr = $dateStr . ' ' . $timeStr;
            $matchTimestamp = strtotime($fullDateTimeStr);

            if ($matchTimestamp === false) {
                $matchTimestamp = time() + 7200;
            }

            $matches[] = [
                'team1' => $team1,
                'team2' => $team2,
                'timestamp' => $matchTimestamp,
                'url' => self::VLR_BASE . $node->getAttribute('href')
            ];
        }

        return $matches;
    }
}