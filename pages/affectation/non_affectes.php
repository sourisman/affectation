<?php
require_once '../../config/db.php';
require_once '../../includes/header.php';

$employes = $pdo->query("
    SELECT e.*, l.design AS nom_lieu
    FROM EMPLOYE e
    LEFT JOIN LIEU l ON e.lieu = l.idlieu
    WHERE e.numEmp NOT IN (
        SELECT DISTINCT numEmp FROM AFFECTER
    )
    ORDER BY e.nom, e.prenom
")->fetchAll();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --electric-orange: #FF6A00; /* Orange éclairé */
        --neon-amber:      --electric-orange;
        --bright-gold:     #FFB300;
        --deep-dark:       #03030a;
        --card-bg:         rgba(12, 8, 20, 0.8);
        --text-main:       #f5f7ff;
        --text-muted:      #8f9bb3;
        --glow-orange:     0 0 14px #FF6A00, 0 0 35px rgba(255,106,0,0.4);
        --glow-gold:       0 0 12px #FFB300, 0 0 25px rgba(255,179,0,0.25);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background: var(--deep-dark);
        background-image:
            radial-gradient(ellipse 75% 55% at 50% 0%,   rgba(255,106,0,0.12) 0%, transparent 65%),
            radial-gradient(ellipse 60% 50% at 50% 100%, rgba(140,40,255,0.06) 0%, transparent 60%);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        min-height: 100vh;
        padding: 3rem 0 5rem;
    }

    /* Grille de points d'ambiance */
    body::before {
        content: ''; position: fixed; inset: 0;
        background-image: radial-gradient(rgba(255,106,0,0.06) 1px, transparent 1px);
        background-size: 32px 32px; pointer-events: none; z-index: 0;
    }

    .page-wrapper { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

    /* Glassmorphism Card Premium */
    .neon-card {
        background: var(--card-bg); border-radius: 24px; backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px);
        padding: 2.5rem; position: relative; overflow: hidden; 
        border: 1.5px solid rgba(255,106,0,0.15); /* Bordure orange initiale subtile */
        box-shadow: 0 10px 45px rgba(0,0,0,0.5);
        transition: box-shadow 0.45s cubic-bezier(.25,.8,.25,1), border-color 0.45s ease;
        animation: riseUp 0.6s cubic-bezier(.22,1,.36,1) both;
    }
    
    /* Ligne de gradient supérieure */
    .neon-card::before { 
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; 
        background: linear-gradient(90deg, var(--electric-orange), var(--bright-gold));
        border-radius: 24px 24px 0 0; 
    }
    
    /* Effet au survol : la bordure s'illumine en orange éclatant */
    .neon-card:hover { 
        box-shadow: var(--glow-orange), inset 0 0 40px rgba(255,106,0,0.02); 
        border-color: rgba(255,106,0,0.6); 
    }

    @keyframes riseUp {
        from { opacity: 0; transform: translateY(25px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* En-tête de la Carte */
    .card-header-flex { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.2rem; }
    .card-title { font-size: 1.45rem; font-weight: 700; letter-spacing: 0.01em; margin: 0; display: flex; align-items: center; gap: 14px; }
    .title-icon { 
        width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        background: rgba(255,106,0,0.15); border: 1px solid rgba(255,106,0,0.25); box-shadow: 0 0 14px rgba(255,106,0,0.2); 
    }
    .title-line { display: block; margin-top: 6px; width: 45px; height: 3px; border-radius: 3px; background: var(--electric-orange); box-shadow: 0 0 8px var(--electric-orange); transition: width 0.4s ease; }
    .neon-card:hover .title-line { width: 85px; }

    /* Compteur de lignes */
    .row-count {
        display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 0.3rem 0.9rem;
    }
    .row-count span { color: var(--electric-orange); font-weight: 700; font-size: 0.9rem; text-shadow: 0 0 8px rgba(255,106,0,0.5); }

    /* Enveloppe de Table de données */
    .table-shell { border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.06); overflow-x: auto; background: rgba(3,3,10,0.2); }
    .neon-table { width: 100%; border-collapse: collapse; color: var(--text-main); font-size: 0.94rem; }
    
    .neon-table thead th {
        background: rgba(255,106,0,0.06); color: var(--electric-orange); font-size: 0.78rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em; padding: 1.2rem 1.4rem; border-bottom: 2px solid rgba(255,106,0,0.3); white-space: nowrap;
    }
    .neon-table tbody td { padding: 1.1rem 1.4rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
    .neon-table tbody tr:last-child td { border-bottom: none; }
    .neon-table tbody tr { transition: background 0.25s ease; }
    .neon-table tbody tr:hover { background: rgba(255,106,0,0.04); }

    /* Éléments de ligne */
    .code-badge {
        display: inline-block; padding: 0.28rem 0.65rem; border-radius: 7px; font-size: 0.78rem; font-weight: 700;
        font-family: 'Courier New', monospace; background: rgba(255,106,0,0.09); color: var(--electric-orange); border: 1px solid rgba(255,106,0,0.25);
    }
    .emp-name { font-weight: 600; color: #fff; }
    .emp-civ { color: var(--text-muted); font-weight: 400; font-size: 0.85rem; margin-right: 3px; }
    .emp-mail { display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted); text-decoration: none; font-size: 0.88rem; transition: color 0.2s; }
    .emp-mail:hover { color: var(--electric-orange); }

    /* État Vide Optimisé (Tous affectés) */
    .empty-state { text-align: center; padding: 5rem 2rem; color: var(--text-muted); }
    .empty-icon { 
        font-size: 3rem; margin-bottom: 1.2rem; display: inline-block;
        text-shadow: 0 0 20px rgba(255,106,0,0.4); animation: pulseGlow 2s infinite alternate;
    }
    @keyframes pulseGlow { from { transform: scale(1); } to { transform: scale(1.08); } }
</style>

<div class="page-wrapper">

    <div class="neon-card animate__animated animate__fadeIn">
        
        <div class="card-header-flex">
            <div class="card-title">
                <div class="title-icon">👥</div>
                <div>
                    <div>Employés jamais affectés</div>
                    <i class="title-line"></i>
                </div>
            </div>
            <div class="row-count">
                <span><?= count($employes) ?></span> agent<?= count($employes) > 1 ? 's' : '' ?> disponible<?= count($employes) > 1 ? 's' : '' ?>
            </div>
        </div>
     
        <?php if (empty($employes)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <p style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-bottom: 0.3rem;">Performance d'affectation maximale !</p>
                <p style="font-style: italic; font-size: 0.92rem;">Tous les employés enregistrés ont déjà été affectés au moins une fois.</p>
            </div>
        <?php else: ?>
            <div class="table-shell">
                <table class="neon-table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">N° Matricule</th>
                            <th style="width: 100px;">Civilité</th>
                            <th>Nom &amp; Prénom</th>
                            <th>Poste Actuel</th>
                            <th>Lieu Initial / Actuel</th>
                            <th>Adresse Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employes as $e): ?>
                        <tr>
                            <td><span class="code-badge"><?= htmlspecialchars($e['numEmp']) ?></span></td>
                            <td><span class="badge bg-dark text-light border border-secondary" style="font-size:0.75rem; padding:0.35rem 0.6rem;"><?= htmlspecialchars($e['civilite']) ?></span></td>
                            <td class="emp-name">
                                <?= htmlspecialchars($e['nom']) ?> <?= htmlspecialchars($e['prenom']) ?>
                            </td>
                            <td style="font-weight: 500; color: rgba(255,255,255,0.85);"><?= htmlspecialchars($e['poste']) ?></td>
                            <td style="color: var(--bright-gold); font-weight: 500;"><?= htmlspecialchars($e['nom_lieu'] ?? 'Non assigné') ?></td>
                            <td>
                                <a href="mailto:<?= urlencode($e['mail']) ?>" class="emp-mail">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <?= htmlspecialchars($e['mail']) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>