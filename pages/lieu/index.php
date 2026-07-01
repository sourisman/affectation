<?php
require_once '../../config/db.php';
require_once '../../includes/header.php';

$msg = '';
if (isset($_GET['msg'])) {
    $type  = $_GET['type'] ?? 'success';
    $texts = [
        'created' => 'Lieu ajouté avec succès.',
        'updated' => 'Lieu modifié avec succès.',
        'deleted' => 'Lieu supprimé avec succès.',
        'used'    => 'Impossible de supprimer : ce lieu est rattaché à un employé.',
        'exists'  => 'Ce code lieu existe déjà.',
    ];
    $text = $texts[$_GET['msg']] ?? htmlspecialchars($_GET['msg']);
    $msg  = "<div class='alert alert-{$type} animate__animated animate__fadeInDown' role='alert'><span class='alert-icon'></span>{$text}</div>";
}

$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY idlieu")->fetchAll();
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

    /* ── Grid de points ambiants ── */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background-image: radial-gradient(rgba(168,85,247,0.12) 1px, transparent 1px);
        background-size: 36px 36px;
        pointer-events: none;
        z-index: 0;
    }

    /* ── Alertes ── */
    .alert {
        max-width: 1200px;
        margin: 0 auto 2rem auto;
        padding: 1.1rem 1.8rem;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.97rem;
        display: flex;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(12px);
        animation: fadeOut 0.4s ease 3.8s forwards;
        position: relative;
        z-index: 10;
    }
    .alert-success {
        background: rgba(163,255,18,0.08);
        color: var(--lime-green);
        border: 1.5px solid rgba(163,255,18,0.5);
        box-shadow: 0 0 18px rgba(163,255,18,0.2), inset 0 0 30px rgba(163,255,18,0.04);
    }
    .alert-success .alert-icon::before { content: '✓'; }
    .alert-danger {
        background: rgba(255,46,196,0.08);
        color: var(--neon-pink);
        border: 1.5px solid rgba(255,46,196,0.5);
        box-shadow: 0 0 18px rgba(255,46,196,0.2), inset 0 0 30px rgba(255,46,196,0.04);
    }
    .alert-danger .alert-icon::before { content: '✕'; }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-8px); } }

    /* ── Layout ── */
    .page-wrapper {
        position: relative;
        z-index: 1;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .cards-row {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .cards-row { grid-template-columns: 1fr; }
    }

    /* ── Glassmorphism cards ── */
    .neon-card {
        background: var(--card-bg);
        border-radius: 22px;
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        padding: 2.2rem 2rem;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.07);
        transition: box-shadow 0.45s ease, border-color 0.45s ease;
        animation: riseUp 0.55s cubic-bezier(.22,1,.36,1) both;
    }
    .neon-card:nth-child(2) { animation-delay: 0.1s; }

    /* Ligne lumineuse top */
    .neon-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 22px 22px 0 0;
    }
    /* Reflet en coin */
    .neon-card::after {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%;
        filter: blur(55px);
        opacity: 0.28;
        pointer-events: none;
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

    /* ── Titres cards ── */
    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 1.8rem;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }
    .card-title .title-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .left-card  .title-icon { background: rgba(0,217,255,0.12); box-shadow: 0 0 12px rgba(0,217,255,0.25); }
    .right-card .title-icon { background: rgba(255,46,196,0.12); box-shadow: 0 0 12px rgba(255,46,196,0.25); }

    .title-text { color: var(--text-main); }
    .title-line {
        display: block;
        margin-top: 8px;
        width: 40px; height: 3px;
        border-radius: 3px;
        transition: width 0.4s ease;
    }
    .left-card  .title-line { background: var(--electric-blue); box-shadow: 0 0 8px var(--electric-blue); }
    .right-card .title-line { background: var(--neon-pink);     box-shadow: 0 0 8px var(--neon-pink); }
    .neon-card:hover .title-line { width: 80px; }

    /* ── Formulaire ── */
    .field-group { margin-bottom: 1.2rem; }
    .form-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.55rem;
    }

    .neon-input {
        width: 100%;
        padding: 0.85rem 1.1rem;
        border: 1.5px solid rgba(255,255,255,0.09);
        border-radius: 12px;
        font-size: 0.97rem;
        background: rgba(3,3,10,0.55);
        color: var(--text-main);
        outline: none;
        transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
    }
    .neon-input::placeholder { color: rgba(136,146,176,0.55); }
    .neon-input:hover  { border-color: rgba(0,217,255,0.3); }
    .neon-input:focus  {
        border-color: var(--electric-blue);
        box-shadow: 0 0 0 3px rgba(0,217,255,0.15), 0 0 16px rgba(0,217,255,0.2);
        background: rgba(3,3,10,0.75);
    }

    /* ── Bouton Enregistrer ── */
    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.85rem 2rem;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--lime-green) 0%, #6be800 100%);
        color: #060c00;
        box-shadow: 0 0 18px rgba(163,255,18,0.35), 0 4px 14px rgba(0,0,0,0.4);
        transition: transform 0.25s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-save::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.25), transparent);
        border-radius: inherit;
    }
    .btn-save:hover  { transform: translateY(-3px); box-shadow: var(--glow-green), 0 8px 20px rgba(0,0,0,0.4); }
    .btn-save:active { transform: translateY(1px);  box-shadow: 0 0 10px rgba(163,255,18,0.3); }

    /* ── Tableau ── */
    .table-shell {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.07);
    }
    .neon-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-main);
        font-size: 0.93rem;
    }
    .neon-table thead th {
        background: rgba(0,217,255,0.07);
        color: var(--electric-blue);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 1rem 1.3rem;
        border-bottom: 2px solid rgba(255,46,196,0.4);
        white-space: nowrap;
    }
    .neon-table tbody td {
        padding: 0.95rem 1.3rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        vertical-align: middle;
    }
    .neon-table tbody tr:last-child td { border-bottom: none; }
    .neon-table tbody tr {
        transition: background 0.25s ease;
    }
    .neon-table tbody tr:hover {
        background: rgba(168,85,247,0.1);
    }

    /* Badge code lieu */
    .code-badge {
        display: inline-block;
        padding: 0.28rem 0.75rem;
        border-radius: 7px;
        font-size: 0.78rem;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.06em;
        background: rgba(0,217,255,0.1);
        color: var(--electric-blue);
        border: 1px solid rgba(0,217,255,0.25);
    }

    /* ── Boutons action ligne ── */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.42rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-decoration: none;
        transition: background 0.25s, color 0.25s, box-shadow 0.25s, transform 0.2s;
        cursor: pointer;
        border: 1.5px solid transparent;
    }
    .btn-edit {
        background: rgba(163,255,18,0.08);
        color: var(--lime-green);
        border-color: rgba(163,255,18,0.3);
    }
    .btn-edit:hover {
        background: var(--lime-green);
        color: #060c00;
        box-shadow: 0 0 14px rgba(163,255,18,0.4);
        transform: translateY(-1px);
    }
    .btn-del {
        background: rgba(255,46,196,0.08);
        color: var(--neon-pink);
        border-color: rgba(255,46,196,0.3);
    }
    .btn-del:hover {
        background: var(--neon-pink);
        color: #fff;
        box-shadow: 0 0 16px rgba(255,46,196,0.5);
        transform: translateY(-1px);
    }
    .btn-action:active { transform: translateY(1px); }
    .actions-cell { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    .empty-state .empty-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.4;
    }
    .empty-state p { font-size: 1rem; font-style: italic; }

    /* ── Compteur de lignes ── */
    .row-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px;
        padding: 0.2rem 0.7rem;
        margin-left: auto;
    }
    .row-count span { color: var(--gamma-violet); font-weight: 700; }
</style>

<?= $msg ?>

<div class="page-wrapper">
    <div class="cards-row">

        <!-- ══ Card Gauche : Formulaire ══ -->
        <div class="neon-card left-card">
            <div class="card-title">
                <div class="title-icon">📍</div>
                <div>
                    <div class="title-text">Ajouter un lieu</div>
                    <i class="title-line"></i>
                </div>
            </div>

            <form action="store.php" method="POST" autocomplete="off">
                <div class="field-group">
                    <label for="idlieu" class="form-label">Code lieu</label>
                    <input type="text" id="idlieu" name="idlieu" maxlength="10"
                           class="neon-input" required placeholder="ex : LIU001">
                </div>
                <div class="field-group">
                    <label for="province" class="form-label">Province</label>
                    <input type="text" id="province" name="province" maxlength="100"
                           class="neon-input" required placeholder="ex : Analamanga">
                </div>
                <div class="field-group">
                    <label for="design" class="form-label">Désignation</label>
                    <input type="text" id="design" name="design" maxlength="100"
                           class="neon-input" required placeholder="ex : Direction Centrale Antananarivo">
                </div>
                <div style="display:flex; justify-content:flex-end; margin-top:1.6rem;">
                    <button type="submit" class="btn-save">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        <!-- ══ Card Droite : Liste ══ -->
        <div class="neon-card right-card">
            <div class="card-title">
                <div class="title-icon">🗺️</div>
                <div>
                    <div class="title-text">Liste des lieux</div>
                    <i class="title-line"></i>
                </div>
                <?php if (!empty($lieux)): ?>
                    <div class="row-count"><span><?= count($lieux) ?></span> entrée<?= count($lieux) > 1 ? 's' : '' ?></div>
                <?php endif; ?>
            </div>

            <?php if (empty($lieux)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🏙️</div>
                    <p>Aucun lieu enregistré.</p>
                </div>
            <?php else: ?>
                <div class="table-shell">
                    <table class="neon-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Désignation</th>
                                <th>Province</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lieux as $l): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($l['idlieu']) ?></span></td>
                                <td><?= htmlspecialchars($l['design']) ?></td>
                                <td><?= htmlspecialchars($l['province']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="edit.php?id=<?= urlencode($l['idlieu']) ?>" class="btn-action btn-edit">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Modifier
                                        </a>
                                        <a href="delete.php?id=<?= urlencode($l['idlieu']) ?>"
                                           class="btn-action btn-del"
                                           onclick="return confirm('Supprimer le lieu <?= htmlspecialchars($l['idlieu'], ENT_QUOTES) ?> ?')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            Supprimer
                                        </a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>