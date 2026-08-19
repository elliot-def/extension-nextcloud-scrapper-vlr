<?php
namespace OCA\VlrCalendar\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TextPlainResponse;
use OCA\VlrCalendar\Service\VlrScraper;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;

class FeedController extends Controller {
    private $scraper;

    // CORRECTION CRUCIALE ICI : $appName avec un 'a' minuscule
    public function __construct(string $appName, IRequest $request, VlrScraper $scraper) {
        parent::__construct($appName, $request);
        $this->scraper = $scraper;
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    #[PublicPage]
    // CORRECTION ICI : La fonction s'appelle désormais index()
    #[NoAdminRequired]
    #[NoCSRFRequired]
    #[PublicPage]
    // Ajout des variables directement dans la fonction
    public function index(string $teams = '', string $players = ''): TextPlainResponse {
        
        // 1. On transforme le texte de l'URL (séparé par des virgules) en tableaux PHP
        $teamsToFollow = $teams !== '' ? array_map('trim', explode(',', $teams)) : [];
        $playersPaths = $players !== '' ? array_map('trim', explode(',', $players)) : [];

        // Si l'utilisateur n'a rien mis, on peut lui mettre une valeur par défaut pour le test
        if (empty($teamsToFollow) && empty($playersPaths)) {
            $teamsToFollow = ['Karmine Corp']; 
        }

        // 2. Résolution des joueurs en équipes
        foreach ($playersPaths as $path) {
            // On s'assure de la bonne forme de l'URL pour VLR (ex: /player/9/tenz)
            $url = '/player/' . ltrim($path, '/player/'); 
            $team = $this->scraper->resolvePlayerTeam($url);
            if ($team && !in_array($team, $teamsToFollow)) {
                $teamsToFollow[] = $team;
            }
        }

        // 3. Récupération des matchs
        $matches = $this->scraper->getMatchesForTeams($teamsToFollow);

        // 4. Génération du fichier ICS
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Nextcloud VLR Calendar//FR\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";

        foreach ($matches as $match) {
            $uid = md5($match['url']) . "@vlr.gg";
            $now = gmdate('Ymd\THis\Z');

            $startTimestamp = $match['timestamp'];
            $dateStart = gmdate('Ymd\THis\Z', $startTimestamp);
            $dateEnd = gmdate('Ymd\THis\Z', $startTimestamp + 7200); // Durée de 2h par match

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:{$uid}\r\n";
            $ics .= "DTSTAMP:{$now}\r\n";
            $ics .= "DTSTART:{$dateStart}\r\n";
            $ics .= "DTEND:{$dateEnd}\r\n";
            $ics .= "SUMMARY:Valorant: {$match['team1']} vs {$match['team2']}\r\n";
            $ics .= "URL:{$match['url']}\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        $response = new TextPlainResponse($ics);
        $response->addHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->addHeader('Content-Disposition', 'attachment; filename="vlr_matches.ics"');
        return $response;
    }
}