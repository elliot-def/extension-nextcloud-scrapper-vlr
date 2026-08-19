<div id="app-content" style="padding: 40px; display: flex; justify-content: center; background-color: var(--color-main-background);">
    <div style="max-width: 600px; width: 100%; background: var(--color-box-background); padding: 30px; border-radius: var(--border-radius-large); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        
        <h2 style="margin-bottom: 10px; font-weight: bold;">📅 VLR.gg Calendar</h2>
        <p style="margin-bottom: 30px; color: var(--color-text-maxcontrast);">
            Configurez vos équipes et joueurs pour générer un lien WebCal personnalisé.
        </p>

        <div style="margin-bottom: 20px;">
            <label for="teams" style="display: block; margin-bottom: 5px; font-weight: bold;">Équipes (séparées par des virgules)</label>
            <input type="text" id="teams" placeholder="ex: Karmine Corp, Fnatic" style="width: 100%; padding: 10px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="players" style="display: block; margin-bottom: 5px; font-weight: bold;">Joueurs (ID et Pseudo, séparés par des virgules)</label>
            <input type="text" id="players" placeholder="ex: 9/tenz, 231/boaster" style="width: 100%; padding: 10px;">
        </div>

        <button id="btn-generate" class="button primary" style="width: 100%; padding: 12px; font-size: 16px;">
            Générer le lien du calendrier
        </button>

        <div id="result-box" style="display: none; margin-top: 30px; padding: 20px; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
            <p style="margin-bottom: 10px; font-weight: bold; color: var(--color-success);">✅ Votre lien est prêt !</p>
            <p style="font-size: 13px; margin-bottom: 10px;">Copiez ce lien et collez-le dans Calendrier > Nouvel abonnement par lien.</p>
            <input type="text" id="final-url" readonly style="width: 100%; padding: 10px; background-color: var(--color-background-dark); cursor: copy;" onclick="this.select();">
        </div>
        
    </div>
</div>

<script>
document.getElementById('btn-generate').addEventListener('click', function() {
    const teamsInput = document.getElementById('teams').value.trim();
    const playersInput = document.getElementById('players').value.trim();
    
    // On utilise la fonction native p() de Nextcloud pour afficher la variable sécurisée passée par le contrôleur
    const baseUrl = "<?php p($_['feedUrl']); ?>";
    
    const params = new URLSearchParams();
    if (teamsInput) params.append('teams', teamsInput);
    if (playersInput) params.append('players', playersInput);
    
    const finalUrl = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;
    
    const urlField = document.getElementById('final-url');
    urlField.value = finalUrl;
    document.getElementById('result-box').style.display = 'block';
});
</script>