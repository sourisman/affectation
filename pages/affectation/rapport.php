<?php
require_once '../../config/db.php';
require_once '../../includes/header.php';

$dateDebut    = trim($_GET['dateDebut']    ?? '');
$dateFin      = trim($_GET['dateFin']      ?? '');
$affectations = [];
$searched     = false;
$erreur       = '';

if ($dateDebut !== '' && $dateFin !== '') {
    if ($dateFin < $dateDebut) {
        $erreur = "La date de fin doit être supérieure ou égale à la date de début.";
    } else {
        $searched = true;
        $stmt = $pdo->prepare("
            SELECT
                a.numAffect,
                a.dateAffect,
                a.datePriseService,
                e.civilite,
                e.nom,
                e.prenom,
                e.poste,
                la.design AS ancienLieu,
                ln.design AS nouveauLieu
            FROM AFFECTER a
            INNER JOIN EMPLOYE e  ON a.numEmp      = e.numEmp
            LEFT  JOIN LIEU    la ON a.ancienLieu  = la.idlieu
            LEFT  JOIN LIEU    ln ON a.nouveauLieu = ln.idlieu
            WHERE a.dateAffect BETWEEN ? AND ?
            ORDER BY a.dateAffect ASC
        ");
        $stmt->execute([$dateDebut, $dateFin]);
        $affectations = $stmt->fetchAll();
    }
}

function fdate($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d/m/Y') : $d;
}
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
        --glow-blue:      0 0 12px #00D9FF, 0 0 30px rgba(0,217,255,0.35);
        --glow-pink:      0 0 12px #FF2EC4, 0 0 30px rgba(255,46,196,0.35);
        --glow-violet:    0 0 20px rgba(168,85,247,0.35);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background: var(--deep-dark);
        background-image:
            radial-gradient(ellipse 80% 60% at 50% 0%,   rgba(168,85,247,0.15) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 90% 100%, rgba(0,217,255,0.12)  0%, transparent 60%),
            radial-gradient(ellipse 50% 40% at 10% 80%,  rgba(255,46,196,0.08) 0%, transparent 55%);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        min-height: 100vh;
        padding: 2.5rem 0 4rem;
    }

    /* Grille de points d'ambiance */
    body::before {
        content: ''; position: fixed; inset: 0;
        background-image: radial-gradient(rgba(168,85,247,0.10) 1px, transparent 1px);
        background-size: 36px 36px; pointer-events: none; z-index: 0;
    }

    .page-wrapper { position: relative; z-index: 1; max-width: 1300px; margin: 0 auto; padding: 0 1.5rem; }

    /* Alertes critiques / erreurs */
    .alert-danger {
        background: rgba(255,46,196,0.08); color: var(--neon-pink); border: 1.5px solid rgba(255,46,196,0.4);
        box-shadow: 0 0 18px rgba(255,46,196,0.15), inset 0 0 30px rgba(255,46,196,0.03);
        border-radius: 14px; font-weight: 600; font-size: 0.95rem; padding: 1.1rem 1.5rem;
        margin-bottom: 1.8rem; display: flex; align-items: center; gap: 10px; backdrop-filter: blur(10px);
    }
    .alert-danger::before { content: '⚠️ '; font-size: 1.1rem; }

    /* Glassmorphism Cards */
    .neon-card {
        background: var(--card-bg); border-radius: 22px; backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);
        padding: 2.2rem 2rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.07);
        margin-bottom: 2rem; box-shadow: 0 4px 40px rgba(0,0,0,0.4);
        transition: box-shadow 0.45s ease, border-color 0.45s ease;
        animation: riseUp 0.55s cubic-bezier(.22,1,.36,1) both;
    }
    .neon-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 22px 22px 0 0; }
    
    .filter-card::before { background: linear-gradient(90deg, var(--electric-blue), var(--gamma-violet)); }
    .filter-card:hover  { box-shadow: var(--glow-blue), inset 0 0 50px rgba(0,217,255,0.02); border-color: rgba(0,217,255,0.25); }

    .result-card::before { background: linear-gradient(90deg, var(--gamma-violet), var(--neon-pink)); }
    .result-card:hover  { box-shadow: var(--glow-violet), inset 0 0 50px rgba(168,85,247,0.02); border-color: rgba(168,85,247,0.25); }

    @keyframes riseUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* En-têtes de sections */
    .card-title { font-size: 1.3rem; font-weight: 700; letter-spacing: 0.02em; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 12px; }
    .card-title .title-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .filter-card .title-icon { background: rgba(0,217,255,0.12); box-shadow: 0 0 12px rgba(0,217,255,0.2); }
    .result-card .title-icon { background: rgba(168,85,247,0.15); box-shadow: 0 0 12px rgba(168,85,247,0.2); }
    .title-line { display: block; margin-top: 6px; width: 40px; height: 3px; border-radius: 3px; transition: width 0.4s ease; }
    .filter-card .title-line { background: var(--electric-blue); box-shadow: 0 0 8px var(--electric-blue); }
    .result-card .title-line { background: var(--gamma-violet);  box-shadow: 0 0 8px var(--gamma-violet); }
    .neon-card:hover .title-line { width: 75px; }

    /* Grille de champs du formulaire */
    .form-row-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
    .field-container { display: flex; flex-direction: column; }
    .form-label { display: block; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.55rem; }
    
    /* Intégration inputs de date */
    .neon-input {
        width: 100%; padding: 0.85rem 1.2rem; border: 1.5px solid rgba(255,255,255,0.09); border-radius: 12px; font-size: 0.98rem;
        background: rgba(3,3,10,0.6); color: var(--text-main); outline: none; transition: all 0.3s ease;
        color-scheme: dark; /* Thème sombre natif sur le sélecteur de calendrier */
    }
    .neon-input:hover { border-color: rgba(0,217,255,0.3); }
    .neon-input:focus { border-color: var(--electric-blue); box-shadow: 0 0 0 3px rgba(0,217,255,0.15), 0 0 16px rgba(0,217,255,0.2); }

    /* Boutons */
    .actions-flex { display: flex; gap: 12px; justify-content: flex-end; align-items: center; }
    .btn-submit-glow {
        display: inline-flex; align-items: center; gap: 8px; padding: 0.85rem 2rem; border-radius: 12px; font-size: 0.95rem; font-weight: 700;
        letter-spacing: 0.04em; border: none; cursor: pointer; background: linear-gradient(135deg, var(--electric-blue) 0%, #00a2ff 100%); color: #020617;
        box-shadow: 0 0 16px rgba(0,217,255,0.3), 0 4px 10px rgba(0,0,0,0.3); transition: all 0.25s ease; white-space: nowrap;
    }
    .btn-submit-glow:hover { transform: translateY(-2px); box-shadow: var(--glow-blue); }
    
    .btn-reset-glass {
        display: inline-flex; align-items: center; padding: 0.85rem 1.6rem; border-radius: 12px; font-size: 0.95rem; font-weight: 600;
        background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid rgba(255,255,255,0.1); text-decoration: none; transition: all 0.2s;
    }
    .btn-reset-glass:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.25); color: #fff; }

    /* Structures des Tableaux */
    .table-shell { border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.07); overflow-x: auto; background: rgba(3,3,10,0.2); }
    .neon-table { width: 100%; border-collapse: collapse; color: var(--text-main); font-size: 0.92rem; }
    .neon-table thead th {
        background: rgba(168,85,247,0.08); color: var(--gamma-violet); font-size: 0.76rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em; padding: 1.1rem 1.3rem; border-bottom: 2px solid rgba(255,46,196,0.3); white-space: nowrap;
    }
    .neon-table tbody td { padding: 1rem 1.3rem; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
    .neon-table tbody tr:last-child td { border-bottom: none; }
    .neon-table tbody tr { transition: background 0.25s ease; }
    .neon-table tbody tr:hover { background: rgba(0,217,255,0.05); }

    .code-badge {
        display: inline-block; padding: 0.28rem 0.65rem; border-radius: 7px; font-size: 0.78rem; font-weight: 700;
        font-family: 'Courier New', monospace; background: rgba(0,217,255,0.08); color: var(--electric-blue); border: 1px solid rgba(0,217,255,0.2);
    }

    /* Bouton Impression Ligne */
    .action-link {
        display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 0.95rem; border-radius: 8px; font-size: 0.82rem;
        font-weight: 600; text-decoration: none; transition: all 0.25s; border: 1.5px solid transparent;
    }
    .link-print { background: rgba(255,255,255,0.05); color: var(--text-main); border-color: rgba(255,255,255,0.15); }
    .link-print:hover { background: #fff; color: #000; box-shadow: 0 0 15px rgba(255,255,255,0.4); transform: translateY(-1px); }

    .row-count {
        display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 600; color: var(--text-muted);
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 0.25rem 0.75rem; margin-left: auto;
    }
    .row-count span { color: var(--neon-pink); font-weight: 700; }

    .empty-state { text-align: center; padding: 4.5rem 2rem; color: var(--text-muted); }
    .empty-icon { font-size: 2.5rem; margin-bottom: 1.2rem; opacity: 0.35; }
</style>

<div class="page-wrapper">

    <div class="neon-card filter-card">
        <div class="card-title">
            <div class="title-icon">📅</div>
            <div>
                <div class="title-text">Affectations par période</div>
                <i class="title-line"></i>
            </div>
        </div>

        <?php if ($erreur): ?>
            <div class="alert alert-danger animate__animated animate__shakeX"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="GET" action="rapport.php">
            <div class="form-row-grid">
                <div class="field-container">
                    <label class="form-label">Date début</label>
                    <input type="date" name="dateDebut" class="neon-input"
                           value="<?= htmlspecialchars($dateDebut) ?>" required>
                </div>
                <div class="field-container">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="dateFin" class="neon-input"
                           value="<?= htmlspecialchars($dateFin) ?>" required>
                </div>
            </div>
            
            <div class="actions-flex">
                <?php if ($searched): ?>
                    <a href="rapport.php" class="btn-reset-glass">Réinitialiser</a>
                <?php endif; ?>
                <button type="submit" class="btn-submit-glow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <?php if ($searched): ?>
        <div class="neon-card result-card animate__animated animate__fadeIn">

            <?php if (empty($affectations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <p style="font-size:1.02rem; font-style: italic;">
                        Aucune affectation enregistrée entre le <strong style="color:#fff;"><?= fdate($dateDebut) ?></strong> et le <strong style="color:#fff;"><?= fdate($dateFin) ?></strong>.
                    </p>
                </div>

            <?php else: ?>
                <div class="card-title">
                    <div class="title-icon">📊</div>
                    <div style="display: flex; width: 100%; align-items: center;">
                        <div>
                            <div class="title-text" style="font-size:1.15rem;">
                                Résultats du <span style="color:#fff; font-weight:600;"><?= fdate($dateDebut) ?></span> au <span style="color:#fff; font-weight:600;"><?= fdate($dateFin) ?></span>
                            </div>
                            <i class="title-line"></i>
                        </div>
                        <div class="row-count">
                            <span><?= count($affectations) ?></span> affectation<?= count($affectations) > 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>

                <div class="table-shell">
                    <table class="neon-table">
                        <thead>
                            <tr>
                                <th>N° Affectation</th>
                                <th>Employé</th>
                                <th>Poste</th>
                                <th>Ancien Lieu</th>
                                <th>Nouveau Lieu</th>
                                <th>Date Affect.</th>
                                <th>Prise Service</th>
                                <th style="text-align: center;">Imprimer</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($affectations as $a): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($a['numAffect']) ?></span></td>
                                <td style="font-weight:600; white-space:nowrap;">
                                    <span style="color:var(--text-muted); font-weight:400; font-size:0.85rem; margin-right:3px;"><?= htmlspecialchars($a['civilite']) ?></span>
                                    <?= htmlspecialchars($a['nom']) ?> <?= htmlspecialchars($a['prenom']) ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.88rem;"><?= htmlspecialchars($a['poste']) ?></td>
                                <td style="color: rgba(255,255,255,0.6); font-size: 0.88rem;"><?= htmlspecialchars($a['ancienLieu'] ?? '-') ?></td>
                                <td style="color: var(--electric-blue); font-weight: 500;"><?= htmlspecialchars($a['nouveauLieu'] ?? '-') ?></td>
                                <td style="white-space: nowrap;"><?= fdate($a['dateAffect']) ?></td>
                                <td style="white-space: nowrap; font-weight: 500;"><?= fdate($a['datePriseService']) ?></td>
                                <td style="text-align: center; width: 110px;">
                                    <a href="../historique/pdf.php?numAffect=<?= urlencode($a['numAffect']) ?>" target="_blank" class="action-link link-print">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><polyline points="6 14 18 14 18 22 6 22"/></svg>
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>