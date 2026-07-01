<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/header.php';

// ─── Messages de Notification ────────────────────────────────────────────────
$msg = '';
if (isset($_GET['msg'])) {
    $type  = $_GET['type'] ?? 'success';
    $texts = [
        'mail_envoye' => 'Notification envoyée avec succès par email.',
        'mail_erreur' => 'Erreur critique lors de l\'envoi du mail.'
    ];
    $text  = $texts[$_GET['msg']] ?? htmlspecialchars($_GET['msg']);
    
    // Alerte stylisée correspondante
    $alertClass = ($type === 'success' || $_GET['msg'] === 'mail_envoye') ? 'alert-success' : 'alert-danger';
    $msg = "<div class='alert {$alertClass} animate__animated animate__fadeInDown' role='alert'><span class='alert-icon'></span>{$text}</div>";
}

// ─── Requêtes de données ─────────────────────────────────────────────────────
$employes = $pdo->query("
    SELECT numEmp, nom, prenom FROM EMPLOYE ORDER BY nom, prenom
")->fetchAll();

$numEmp       = trim($_GET['numEmp'] ?? '');
$affectations = [];
$employe      = null;

if ($numEmp !== '') {
    $stmt = $pdo->prepare("SELECT numEmp, nom, prenom FROM EMPLOYE WHERE numEmp = ?");
    $stmt->execute([$numEmp]);
    $employe = $stmt->fetch();

    if ($employe) {
        $stmt = $pdo->prepare("
            SELECT a.numAffect,
                   a.numEmp,
                   a.dateAffect,
                   a.datePriseService,
                   a.ancienLieu,
                   a.nouveauLieu,
                   la.design AS nom_ancien,
                   ln.design AS nom_nouveau
            FROM AFFECTER a
            LEFT JOIN LIEU la ON a.ancienLieu  = la.idlieu
            LEFT JOIN LIEU ln ON a.nouveauLieu = ln.idlieu
            WHERE a.numEmp = ?
            ORDER BY a.dateAffect DESC
        ");
        $stmt->execute([$numEmp]);
        $affectations = $stmt->fetchAll();
    }
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
        --glow-green:     0 0 12px #A3FF12, 0 0 25px rgba(163,255,18,0.4);
        --glow-violet:    0 0 20px rgba(168,85,247,0.35);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background: var(--deep-dark);
        background-image:
            radial-gradient(ellipse 80% 60% at 50% 0%,   rgba(168,85,247,0.15) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 10% 100%, rgba(0,217,255,0.12)  0%, transparent 60%),
            radial-gradient(ellipse 50% 40% at 90% 80%,  rgba(255,46,196,0.08) 0%, transparent 55%);
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

    .page-wrapper { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

    /* Alertes Flash */
    .alert {
        max-width: 1150px; margin: 0 auto 2rem auto; padding: 1.1rem 1.8rem;
        border-radius: 14px; font-weight: 600; font-size: 0.97rem;
        display: flex; align-items: center; gap: 10px; backdrop-filter: blur(12px);
        animation: fadeOut 0.4s ease 4.5s forwards; position: relative; z-index: 10;
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
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-8px); visibility: hidden; } }

    /* Glassmorphism Cards */
    .neon-card {
        background: var(--card-bg); border-radius: 22px; backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);
        padding: 2.2rem 2rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.07);
        margin-bottom: 2rem; box-shadow: 0 4px 40px rgba(0,0,0,0.3);
        transition: box-shadow 0.45s ease, border-color 0.45s ease;
        animation: riseUp 0.55s cubic-bezier(.22,1,.36,1) both;
    }
    .neon-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 22px 22px 0 0; }
    
    .filter-card::before { background: linear-gradient(90deg, var(--electric-blue), var(--gamma-violet)); }
    .filter-card:hover  { box-shadow: var(--glow-blue), inset 0 0 50px rgba(0,217,255,0.02); border-color: rgba(0,217,255,0.25); }

    .result-card::before { background: linear-gradient(90deg, var(--gamma-violet), var(--neon-pink)); }
    .result-card:hover  { box-shadow: var(--glow-violet), inset 0 0 50px rgba(168,85,247,0.02); border-color: rgba(168,85,247,0.25); }

    @keyframes riseUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* En-têtes */
    .card-title { font-size: 1.3rem; font-weight: 700; letter-spacing: 0.02em; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 12px; }
    .card-title .title-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .filter-card .title-icon { background: rgba(0,217,255,0.12); box-shadow: 0 0 12px rgba(0,217,255,0.2); }
    .result-card .title-icon { background: rgba(168,85,247,0.15); box-shadow: 0 0 12px rgba(168,85,247,0.2); }
    .title-line { display: block; margin-top: 6px; width: 40px; height: 3px; border-radius: 3px; transition: width 0.4s ease; }
    .filter-card .title-line { background: var(--electric-blue); box-shadow: 0 0 8px var(--electric-blue); }
    .result-card .title-line { background: var(--gamma-violet);  box-shadow: 0 0 8px var(--gamma-violet); }
    .neon-card:hover .title-line { width: 75px; }

    /* Eléments de Formulaire */
    .form-row-flex { display: flex; gap: 1.2rem; align-items: flex-end; flex-wrap: wrap; }
    .field-container { flex: 1; min-width: 280px; }
    .form-label { display: block; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.55rem; }
    
    .neon-select {
        width: 100%; padding: 0.85rem 1.2rem; border: 1.5px solid rgba(255,255,255,0.09); border-radius: 12px; font-size: 0.98rem;
        background: rgba(3,3,10,0.6); color: var(--text-main); outline: none; transition: all 0.3s ease;
        appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill="%2300D9FF" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
        background-repeat: no-repeat; background-position: right 1.2rem center; background-size: 1.3rem; padding-right: 2.8rem;
    }
    .neon-select:hover { border-color: rgba(0,217,255,0.3); }
    .neon-select:focus { border-color: var(--electric-blue); box-shadow: 0 0 0 3px rgba(0,217,255,0.15), 0 0 16px rgba(0,217,255,0.2); }
    .neon-select option { background: #0a0818; color: #fff; }
    .neon-select[color-scheme="dark"] { color-scheme: dark; }

    /* Boutons Tech */
    .btn-submit-glow {
        display: inline-flex; align-items: center; gap: 8px; padding: 0.85rem 1.8rem; border-radius: 12px; font-size: 0.95rem; font-weight: 700;
        letter-spacing: 0.04em; border: none; cursor: pointer; background: linear-gradient(135deg, var(--electric-blue) 0%, #00a2ff 100%); color: #020617;
        box-shadow: 0 0 16px rgba(0,217,255,0.3), 0 4px 10px rgba(0,0,0,0.3); transition: all 0.25s ease; white-space: nowrap;
    }
    .btn-submit-glow:hover { transform: translateY(-2px); box-shadow: var(--glow-blue); }
    
    .btn-reset-glass {
        display: inline-flex; align-items: center; padding: 0.85rem 1.5rem; border-radius: 12px; font-size: 0.95rem; font-weight: 600;
        background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid rgba(255,255,255,0.1); text-decoration: none; transition: all 0.2s;
    }
    .btn-reset-glass:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.25); color: #fff; }

    /* Tableau & Listes */
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

    /* Badges / Boutons de lignes */
    .action-link {
        display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 0.95rem; border-radius: 8px; font-size: 0.82rem;
        font-weight: 600; text-decoration: none; transition: all 0.25s; border: 1.5px solid transparent;
    }
    .link-print { background: rgba(255,255,255,0.05); color: var(--text-main); border-color: rgba(255,255,255,0.15); }
    .link-print:hover { background: #fff; color: #000; box-shadow: 0 0 15px rgba(255,255,255,0.4); transform: translateY(-1px); }
    
    .link-notify { background: rgba(163,255,12,0.06); color: var(--lime-green); border-color: rgba(163,255,12,0.25); }
    .link-notify:hover { background: var(--lime-green); color: #03030a; box-shadow: var(--glow-green); transform: translateY(-1px); }

    .row-count {
        display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 600; color: var(--text-muted);
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 0.25rem 0.75rem; margin-left: auto;
    }
    .row-count span { color: var(--neon-pink); font-weight: 700; }

    .empty-state {
        text-align: center; padding: 4rem 2rem; color: var(--text-muted);
    }
    .empty-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.35; }
</style>

<div class="page-wrapper">
    
    <?= $msg ?>

    <div class="neon-card filter-card">
        <div class="card-title">
            <div class="title-icon">🔍</div>
            <div>
                <div class="title-text">Historique des affectations</div>
                <i class="title-line"></i>
            </div>
        </div>
        
        <form method="GET" action="index.php">
            <div class="form-row-flex">
                <div class="field-container">
                    <label class="form-label">Choisir un employé</label>
                    <select name="numEmp" class="neon-select" required>
                        <option value="">-- Sélectionner un employé --</option>
                        <?php foreach ($employes as $e): ?>
                            <option value="<?= htmlspecialchars($e['numEmp']) ?>" <?= $numEmp === $e['numEmp'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-submit-glow">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="margin-bottom:1px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Afficher
                    </button>
                    <?php if ($numEmp !== ''): ?>
                        <a href="index.php" class="btn-reset-glass">Réinitialiser</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if ($numEmp !== ''): ?>
        <div class="neon-card result-card animate__animated animate__fadeIn">

            <?php if (!$employe): ?>
                <div class="empty-state">
                    <div class="empty-icon">🛸</div>
                    <p style="font-style: italic;">Employé introuvable dans la base de données.</p>
                </div>

            <?php elseif (empty($affectations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📁</div>
                    <p style="font-size:1.05rem;">
                        Aucune affectation enregistrée pour 
                        <strong style="color:var(--text-main); font-weight:600;">
                            <?= htmlspecialchars($employe['nom']) ?> <?= htmlspecialchars($employe['prenom']) ?>
                        </strong>.
                    </p>
                </div>

            <?php else: ?>
                <div class="card-title">
                    <div class="title-icon">📊</div>
                    <div style="display: flex; width: 100%; align-items: center;">
                        <div>
                            <div class="title-text" style="font-size:1.15rem;">
                                Affectations de <span style="color:#fff; font-weight:600;"><?= htmlspecialchars($employe['nom']) ?> <?= htmlspecialchars($employe['prenom']) ?></span>
                            </div>
                            <i class="title-line"></i>
                        </div>
                        <div class="row-count">
                            <span><?= count($affectations) ?></span> archivage<?= count($affectations) > 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>

                <div class="table-shell">
                    <table class="neon-table">
                        <thead>
                            <tr>
                                <th>N° Affectation</th>
                                <th>Ancien Lieu</th>
                                <th>Nouveau Lieu</th>
                                <th>Date Affectation</th>
                                <th>Date Prise Service</th>
                                <th style="text-align: center;">Imprimer</th>
                                <th style="text-align: center;">Notifier</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($affectations as $a): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($a['numAffect']) ?></span></td>
                                <td style="color: var(--text-muted); font-size: 0.88rem;"><?= htmlspecialchars($a['nom_ancien'] ?? 'Non spécifié') ?></td>
                                <td style="color: var(--electric-blue); font-weight: 500;"><?= htmlspecialchars($a['nom_nouveau'] ?? '-') ?></td>
                                <td style="white-space: nowrap;"><?= htmlspecialchars($a['dateAffect']) ?></td>
                                <td style="white-space: nowrap; font-weight: 500;"><?= htmlspecialchars($a['datePriseService']) ?></td>
                                <td style="text-align: center; width: 110px;">
                                    <a href="pdf.php?numAffect=<?= urlencode($a['numAffect']) ?>" target="_blank" class="action-link link-print">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><polyline points="6 14 18 14 18 22 6 22"/></svg>
                                        PDF
                                    </a>
                                </td>
                                <td style="text-align: center; width: 120px;">
                                    <a href="notification.php?numAffect=<?= urlencode($a['numAffect']) ?>&numEmp=<?= urlencode($numEmp) ?>" 
                                       class="action-link link-notify"
                                       onclick="return confirm('Envoyer la notification d\'affectation par courriel à cet employé ?')">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        Mail
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>