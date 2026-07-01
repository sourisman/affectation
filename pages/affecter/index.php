<?php
require_once '../../config/db.php';
require_once '../../includes/header.php';

// ─── Messages Flash ──────────────────────────────────────────────────────────
$msg = '';
if (isset($_GET['msg'])) {
    $type  = $_GET['type'] ?? 'success';
    $texts = [
        'created'    => 'Affectation ajoutée avec succès.',
        'updated'    => 'Affectation modifiée avec succès.',
        'deleted'    => 'Affectation supprimée avec succès.',
        'exists'     => 'Ce numéro d\'affectation existe déjà.',
        'date_error' => 'La date de prise de service doit être >= à la date d\'affectation.',
        'same_lieu'  => 'L\'ancien lieu et le nouveau lieu doivent être différents.',
    ];
    $text = $texts[$_GET['msg']] ?? htmlspecialchars($_GET['msg']);
    $msg  = "<div class='alert alert-{$type} animate__animated animate__fadeInDown' role='alert'><span class='alert-icon'></span>{$text}</div>";
}

// ─── Données de la base ──────────────────────────────────────────────────────
$employes = $pdo->query("
    SELECT numEmp, nom, prenom, lieu FROM EMPLOYE ORDER BY nom, prenom
")->fetchAll();

$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY design")->fetchAll();

// Tableau lieu idlieu => design pour affichage
$lieuxMap = [];
foreach ($lieux as $l) {
    $lieuxMap[$l['idlieu']] = $l['design'];
}

// Tableau employé => lieu actuel pour JS
$empLieuMap = [];
foreach ($employes as $e) {
    $empLieuMap[$e['numEmp']] = $e['lieu'];
}

$affectations = $pdo->query("
    SELECT a.*, e.nom, e.prenom
    FROM AFFECTER a
    INNER JOIN EMPLOYE e ON a.numEmp = e.numEmp
    ORDER BY a.dateAffect DESC
")->fetchAll();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --electric-blue:  #00D9FF;
        --neon-pink:      #FF2EC4;
        --gamma-violet:   #A855F7;
        --lime-green:     #A3FF12;
        --deep-dark:      #03030a;
        --card-bg:        rgba(10, 8, 24, 0.75);
        --text-main:      #f0f4ff;
        --text-muted:     #8892b0;
        --glow-blue:      0 0 12px #00D9FF, 0 0 30px rgba(0,217,255,0.35), 0 0 60px rgba(0,217,255,0.12);
        --glow-pink:      0 0 12px #FF2EC4, 0 0 30px rgba(255,46,196,0.35), 0 0 60px rgba(255,46,196,0.12);
        --glow-green:     0 0 12px #A3FF12, 0 0 25px rgba(163,255,18,0.4);
        --glow-violet:    0 0 20px rgba(168,85,247,0.4);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background: var(--deep-dark);
        background-image:
            radial-gradient(ellipse 80% 60% at 20% 0%,   rgba(168,85,247,0.18) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 80% 100%, rgba(0,217,255,0.14)  0%, transparent 60%),
            radial-gradient(ellipse 50% 40% at 90% 20%,  rgba(255,46,196,0.10) 0%, transparent 55%);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        min-height: 100vh;
        padding: 2.5rem 0 3rem;
    }

    /* Grid de points ambiants */
    body::before {
        content: ''; position: fixed; inset: 0;
        background-image: radial-gradient(rgba(168,85,247,0.12) 1px, transparent 1px);
        background-size: 36px 36px; pointer-events: none; z-index: 0;
    }

    /* Alertes */
    .alert {
        max-width: 1400px; margin: 0 auto 2rem auto; padding: 1.1rem 1.8rem;
        border-radius: 14px; font-weight: 600; font-size: 0.97rem;
        display: flex; align-items: center; gap: 10px; backdrop-filter: blur(12px);
        animation: fadeOut 0.4s ease 3.8s forwards; position: relative; z-index: 10;
    }
    .alert-success {
        background: rgba(163,255,18,0.08); color: var(--lime-green); border: 1.5px solid rgba(163,255,18,0.5);
        box-shadow: 0 0 18px rgba(163,255,18,0.2), inset 0 0 30px rgba(163,255,18,0.04);
    }
    .alert-success .alert-icon::before { content: '✓ '; }
    .alert-danger {
        background: rgba(255,46,196,0.08); color: var(--neon-pink); border: 1.5px solid rgba(255,46,196,0.5);
        box-shadow: 0 0 18px rgba(255,46,196,0.2), inset 0 0 30px rgba(255,46,196,0.04);
    }
    .alert-danger .alert-icon::before { content: '✕ '; }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-8px); } }

    /* Layout */
    .page-wrapper { position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; }
    .cards-row { display: grid; grid-template-columns: 380px 1fr; gap: 2rem; align-items: start; }
    @media (max-width: 950px) { .cards-row { grid-template-columns: 1fr; } }

    /* Glassmorphism cards */
    .neon-card {
        background: var(--card-bg); border-radius: 22px; backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);
        padding: 2.2rem 2rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.07);
        transition: box-shadow 0.45s ease, border-color 0.45s ease; animation: riseUp 0.55s cubic-bezier(.22,1,.36,1) both;
    }
    .neon-card:nth-child(2) { animation-delay: 0.1s; }
    .neon-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 22px 22px 0 0; }
    .neon-card::after {
        content: ''; position: absolute; top: -40px; right: -40px; width: 180px; height: 180px;
        border-radius: 50%; filter: blur(55px); opacity: 0.28; pointer-events: none;
    }

    .left-card::before  { background: linear-gradient(90deg, var(--electric-blue), var(--gamma-violet)); }
    .left-card::after   { background: var(--electric-blue); }
    .left-card { box-shadow: 0 4px 40px rgba(0,217,255,0.12), inset 0 0 50px rgba(0,217,255,0.03); }
    .left-card:hover    { box-shadow: var(--glow-blue), inset 0 0 50px rgba(0,217,255,0.05); border-color: rgba(0,217,255,0.3); }

    .right-card::before { background: linear-gradient(90deg, var(--neon-pink), var(--gamma-violet)); }
    .right-card::after  { background: var(--neon-pink); }
    .right-card { box-shadow: 0 4px 40px rgba(255,46,196,0.12), inset 0 0 50px rgba(168,85,247,0.04); }
    .right-card:hover   { box-shadow: var(--glow-pink), inset 0 0 50px rgba(255,46,196,0.05); border-color: rgba(255,46,196,0.3); }

    @keyframes riseUp {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Titres cards */
    .card-title { font-size: 1.25rem; font-weight: 700; letter-spacing: 0.03em; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 10px; position: relative; }
    .card-title .title-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .left-card  .title-icon { background: rgba(0,217,255,0.12); box-shadow: 0 0 12px rgba(0,217,255,0.25); }
    .right-card .title-icon { background: rgba(255,46,196,0.12); box-shadow: 0 0 12px rgba(255,46,196,0.25); }
    .title-text { color: var(--text-main); }
    .title-line { display: block; margin-top: 8px; width: 40px; height: 3px; border-radius: 3px; transition: width 0.4s ease; }
    .left-card  .title-line { background: var(--electric-blue); box-shadow: 0 0 8px var(--electric-blue); }
    .right-card .title-line { background: var(--neon-pink);     box-shadow: 0 0 8px var(--neon-pink); }
    .neon-card:hover .title-line { width: 80px; }

    /* Formulaire */
    .field-group { margin-bottom: 1.2rem; }
    .form-label { display: block; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.55rem; }
    .neon-input {
        width: 100%; padding: 0.85rem 1.1rem; border: 1.5px solid rgba(255,255,255,0.09); border-radius: 12px; font-size: 0.97rem;
        background: rgba(3,3,10,0.55); color: var(--text-main); outline: none; transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }
    .neon-input::placeholder { color: rgba(136,146,176,0.55); }
    .neon-input:hover  { border-color: rgba(0,217,255,0.3); }
    .neon-input:focus  { border-color: var(--electric-blue); box-shadow: 0 0 0 3px rgba(0,217,255,0.15), 0 0 16px rgba(0,217,255,0.2); background: rgba(3,3,10,0.75); }
    
    /* Input désactivé (Ancien Lieu automatique) */
    .neon-input:disabled { opacity: 0.55; cursor: not-allowed; border-color: rgba(255,255,255,0.04); background: rgba(0,0,0,0.3); color: var(--text-muted); }

    /* Forcer le thème sombre sur les inputs natifs (calendrier/select) */
    .neon-input[type="date"] { color-scheme: dark; }
    select.neon-input {
        appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill="%2300D9FF" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
        background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2rem; padding-right: 2.5rem;
    }
    select.neon-input option { background: #080816; color: #fff; }

    /* Boutons */
    .btn-save {
        display: inline-flex; align-items: center; gap: 8px; padding: 0.85rem 2rem; border-radius: 12px; font-size: 0.95rem; font-weight: 700;
        letter-spacing: 0.04em; border: none; cursor: pointer; background: linear-gradient(135deg, var(--lime-green) 0%, #6be800 100%); color: #060c00;
        box-shadow: 0 0 18px rgba(163,255,18,0.35), 0 4px 14px rgba(0,0,0,0.4); transition: transform 0.25s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;
    }
    .btn-save::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.25), transparent); border-radius: inherit; }
    .btn-save:hover  { transform: translateY(-3px); box-shadow: var(--glow-green), 0 8px 20px rgba(0,0,0,0.4); }
    .btn-save:active { transform: translateY(1px);  box-shadow: 0 0 10px rgba(163,255,18,0.3); }

    .btn-cancel {
        background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid rgba(255,255,255,0.1);
        padding: 0.85rem 1.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .btn-cancel:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.25); }

    /* Tableau */
    .table-shell { border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.07); overflow-x: auto; }
    .neon-table { width: 100%; border-collapse: collapse; color: var(--text-main); font-size: 0.92rem; }
    .neon-table thead th {
        background: rgba(0,217,255,0.07); color: var(--electric-blue); font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em; padding: 1rem 1.3rem; border-bottom: 2px solid rgba(255,46,196,0.4); white-space: nowrap;
    }
    .neon-table tbody td { padding: 0.95rem 1.3rem; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
    .neon-table tbody tr:last-child td { border-bottom: none; }
    .neon-table tbody tr { transition: background 0.25s ease; }
    .neon-table tbody tr:hover { background: rgba(168,85,247,0.1); }

    .code-badge {
        display: inline-block; padding: 0.28rem 0.75rem; border-radius: 7px; font-size: 0.78rem; font-weight: 700;
        font-family: 'Courier New', monospace; letter-spacing: 0.06em; background: rgba(0,217,255,0.1); color: var(--electric-blue); border: 1px solid rgba(0,217,255,0.25);
    }

    /* Actions bouton ligne */
    .btn-action {
        display: inline-flex; align-items: center; gap: 5px; padding: 0.42rem 0.9rem; border-radius: 8px; font-size: 0.8rem;
        font-weight: 600; letter-spacing: 0.03em; text-decoration: none; transition: all 0.25s; cursor: pointer; border: 1.5px solid transparent;
    }
    .btn-edit { background: rgba(163,255,18,0.08); color: var(--lime-green); border-color: rgba(163,255,18,0.3); }
    .btn-edit:hover { background: var(--lime-green); color: #060c00; box-shadow: 0 0 14px rgba(163,255,18,0.4); transform: translateY(-1px); }
    .btn-del { background: rgba(255,46,196,0.08); color: var(--neon-pink); border-color: rgba(255,46,196,0.3); }
    .btn-del:hover { background: var(--neon-pink); color: #fff; box-shadow: 0 0 16px rgba(255,46,196,0.5); transform: translateY(-1px); }
    .actions-cell { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    /* Compteur */
    .row-count {
        display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted);
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 0.2rem 0.7rem; margin-left: auto;
    }
    .row-count span { color: var(--gamma-violet); font-weight: 700; }

    /* Fenêtres Modales Modernisées */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 9999; background: rgba(3, 3, 10, 0.75); backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.3s ease; padding: 1rem;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    
    .neon-modal {
        background: var(--card-bg); border-radius: 22px; border: 1px solid rgba(255,255,255,0.1); padding: 2.2rem; width: 100%; max-width: 580px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5), inset 0 0 40px rgba(168,85,247,0.05); transform: translateY(20px) scale(0.95); transition: all 0.3s ease;
        position: relative; overflow: hidden;
    }
    .modal-overlay.active .neon-modal { transform: translateY(0) scale(1); }
    .neon-modal::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--electric-blue), var(--gamma-violet)); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border: none; padding: 0; }
    .modal-title { font-size: 1.3rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
    .modal-close { background: none; border: none; color: var(--text-muted); font-size: 1.8rem; cursor: pointer; transition: color 0.2s; line-height: 1; }
    .modal-close:hover { color: var(--neon-pink); }
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
    .full-width { grid-column: 1 / -1; }
    .modal-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
</style>

<?= $msg ?>

<div class="page-wrapper">
    <div class="cards-row">

        <div class="neon-card left-card">
            <div class="card-title">
                <div class="title-icon">🔄</div>
                <div>
                    <div class="title-text">Ajouter une affectation</div>
                    <i class="title-line"></i>
                </div>
            </div>

            <form action="store.php" method="POST" autocomplete="off" id="form-ajout">
                <div class="field-group">
                    <label class="form-label">Numéro Affectation</label>
                    <input type="text" name="numAffect" maxlength="15" class="neon-input" required placeholder="ex: AFF001">
                </div>

                <div class="field-group">
                    <label class="form-label">Employé</label>
                    <select name="numEmp" id="sel_employe" class="neon-input" required onchange="remplirAncienLieu()">
                        <option value="">-- Choisir --</option>
                        <?php foreach ($employes as $e): ?>
                            <option value="<?= htmlspecialchars($e['numEmp']) ?>" data-lieu="<?= htmlspecialchars($e['lieu']) ?>">
                                <?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="form-label">Ancien lieu <small style="color:var(--electric-blue);text-transform:none;">(automatique)</small></label>
                    <input type="text" id="ancienLieu_display" class="neon-input" disabled placeholder="Sélectionnez un employé">
                    <input type="hidden" name="ancienLieu" id="ancienLieu_val">
                </div>

                <div class="field-group">
                    <label class="form-label">Nouveau lieu</label>
                    <select name="nouveauLieu" class="neon-input" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($lieux as $l): ?>
                            <option value="<?= htmlspecialchars($l['idlieu']) ?>">
                                <?= htmlspecialchars($l['design']) ?> (<?= htmlspecialchars($l['province']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="form-label">Date Affectation</label>
                    <input type="date" name="dateAffect" id="dateAffect" class="neon-input" required>
                </div>

                <div class="field-group">
                    <label class="form-label">Date Prise Service</label>
                    <input type="date" name="datePriseService" id="datePriseService" class="neon-input" required>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:1.6rem;">
                    <button type="submit" class="btn-save">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        <div class="neon-card right-card">
            <div class="card-title">
                <div class="title-icon">📋</div>
                <div>
                    <div class="title-text">Liste des affectations</div>
                    <i class="title-line"></i>
                </div>
                <?php if (!empty($affectations)): ?>
                    <div class="row-count"><span><?= count($affectations) ?></span> entrée<?= count($affectations) > 1 ? 's' : '' ?></div>
                <?php endif; ?>
            </div>

            <?php if (empty($affectations)): ?>
                <div style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;">🔄</div>
                    <p style="font-size: 1rem; font-style: italic;">Aucune affectation enregistrée.</p>
                </div>
            <?php else: ?>
                <div class="table-shell">
                    <table class="neon-table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Employé</th>
                                <th>Ancien Lieu</th>
                                <th>Nouveau Lieu</th>
                                <th>Date Affect.</th>
                                <th>Prise Service</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($affectations as $a): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($a['numAffect']) ?></span></td>
                                <td style="font-weight:600; white-space:nowrap;"><?= htmlspecialchars($a['nom']) ?> <?= htmlspecialchars($a['prenom']) ?></td>
                                <td style="color: var(--text-muted); font-size:0.88rem;"><?= htmlspecialchars($lieuxMap[$a['ancienLieu']] ?? $a['ancienLieu']) ?></td>
                                <td style="color: var(--electric-blue); font-weight:500;"><?= htmlspecialchars($lieuxMap[$a['nouveauLieu']] ?? $a['nouveauLieu']) ?></td>
                                <td style="white-space:nowrap;"><?= htmlspecialchars($a['dateAffect']) ?></td>
                                <td style="white-space:nowrap;"><?= htmlspecialchars($a['datePriseService']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-action btn-edit"
                                            onclick="ouvrirModif(
                                                '<?= htmlspecialchars($a['numAffect'],        ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($a['nouveauLieu'],      ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($a['dateAffect'],       ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($a['datePriseService'], ENT_QUOTES) ?>'
                                            )">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Modifier
                                        </button>
                                        <button class="btn-action btn-del"
                                            onclick="ouvrirSuppr(
                                                '<?= htmlspecialchars($a['numAffect'], ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($a['nom'],       ENT_QUOTES) ?> <?= htmlspecialchars($a['prenom'], ENT_QUOTES) ?>'
                                            )">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<div id="modal-modif" class="modal-overlay">
    <div class="neon-modal">
        <div class="modal-header">
            <div class="modal-title">✏️ Modifier l'affectation</div>
            <button type="button" class="modal-close" onclick="fermerModif()">✕</button>
        </div>
        <form action="update.php" method="POST" id="form-modif">
            <input type="hidden" name="numAffect" id="m_numAffect">
            <div class="modal-grid">
                <div class="field-group full-width">
                    <label class="form-label">Nouveau lieu</label>
                    <select name="nouveauLieu" id="m_nouveauLieu" class="neon-input" required>
                        <?php foreach ($lieux as $l): ?>
                            <option value="<?= htmlspecialchars($l['idlieu']) ?>">
                                <?= htmlspecialchars($l['design']) ?> (<?= htmlspecialchars($l['province']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group">
                    <label class="form-label">Date Affectation</label>
                    <input type="date" name="dateAffect" id="m_dateAffect" class="neon-input" required>
                </div>
                <div class="field-group">
                    <label class="form-label">Date Prise Service</label>
                    <input type="date" name="datePriseService" id="m_datePriseService" class="neon-input" required>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="fermerModif()">Annuler</button>
                <button type="submit" class="btn-save">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-suppr" class="modal-overlay">
    <div class="neon-modal" style="max-width: 440px; text-align: center;">
        <div class="modal-header" style="justify-content: center; margin-bottom: 1rem;">
            <div class="modal-title" style="color: var(--neon-pink);">⚠️ Confirmation</div>
        </div>
        <p id="suppr-msg" style="margin-bottom: 2rem; line-height: 1.5; color: var(--text-muted); font-size:0.95rem;"></p>
        <form action="delete.php" method="POST">
            <input type="hidden" name="numAffect" id="s_numAffect">
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button type="button" class="btn-cancel" onclick="fermerSuppr()">Annuler</button>
                <button type="submit" class="btn-save" style="background: linear-gradient(135deg, var(--neon-pink) 0%, #ff0055 100%); color: #fff; box-shadow: 0 0 18px rgba(255,46,196,0.35);">
                    Supprimer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Données encodées pour la correspondance dynamique
const empLieu = <?= json_encode($empLieuMap) ?>;
const lieuNom = <?= json_encode($lieuxMap) ?>;

// Remplissage automatique de l'ancien lieu
function remplirAncienLieu() {
    const sel   = document.getElementById('sel_employe');
    const idEmp = sel.value;
    const idLieu = empLieu[idEmp] || '';
    document.getElementById('ancienLieu_val').value     = idLieu;
    document.getElementById('ancienLieu_display').value = lieuNom[idLieu] || '';
}

// Validation des dates (Ajout)
document.getElementById('form-ajout').addEventListener('submit', function(e) {
    const d1 = document.getElementById('dateAffect').value;
    const d2 = document.getElementById('datePriseService').value;
    if (d1 && d2 && d2 < d1) {
        e.preventDefault();
        alert('La date de prise de service doit être supérieure ou égale à la date d\'affectation.');
    }
});

// Validation des dates (Modification)
document.getElementById('form-modif').addEventListener('submit', function(e) {
    const d1 = document.getElementById('m_dateAffect').value;
    const d2 = document.getElementById('m_datePriseService').value;
    if (d1 && d2 && d2 < d1) {
        e.preventDefault();
        alert('La date de prise de service doit être supérieure ou égale à la date d\'affectation.');
    }
});

// Modal Modification (Utilisation des classes .active pour des transitions fluides)
function ouvrirModif(numAffect, nouveauLieu, dateAffect, datePriseService) {
    document.getElementById('m_numAffect').value        = numAffect;
    document.getElementById('m_nouveauLieu').value      = nouveauLieu;
    document.getElementById('m_dateAffect').value       = dateAffect;
    document.getElementById('m_datePriseService').value = datePriseService;
    document.getElementById('modal-modif').classList.add('active');
}
function fermerModif() {
    document.getElementById('modal-modif').classList.remove('active');
}

// Modal Suppression
function ouvrirSuppr(numAffect, nomComplet) {
    document.getElementById('s_numAffect').value = numAffect;
    document.getElementById('suppr-msg').innerHTML =
        'Voulez-vous vraiment supprimer l\'affectation <br>de <strong style="color: #fff;">' + nomComplet + '</strong> ?';
    document.getElementById('modal-suppr').classList.add('active');
}
function fermerSuppr() {
    document.getElementById('modal-suppr').classList.remove('active');
}

// Fermeture des modales au clic en dehors du cadre
window.addEventListener('click', function(e) {
    if (e.target.id === 'modal-modif') fermerModif();
    if (e.target.id === 'modal-suppr') fermerSuppr();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>