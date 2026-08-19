<?php
namespace OCA\VlrCalendar\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class PageController extends Controller {
    private $urlGenerator;

    // Ajout du générateur d'URL dans les paramètres
    public function __construct(string $appName, IRequest $request, IURLGenerator $urlGenerator) {
        parent::__construct($appName, $request);
        $this->urlGenerator = $urlGenerator;
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        // On génère l'URL ici, du côté serveur
        $feedUrl = $this->urlGenerator->linkToRouteAbsolute('vlr_calendar.feed.index');

        // On envoie la variable 'feedUrl' au fichier templates/main.php
        return new TemplateResponse($this->appName, 'main', [
            'feedUrl' => $feedUrl
        ]);
    }
}