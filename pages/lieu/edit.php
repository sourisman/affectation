<?php
require_once '../../config/db.php';
require_once '../../includes/header.php';

$id   = $_GET['id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM LIEU WHERE idlieu = ?");
$stmt->execute([$id]);
$lieu = $stmt->fetch();

if (!$lieu) {
    header('Location: index.php?msg=Lieu+introuvable&type=danger');
    exit;
}
?>

<style>
    :root {
        --bg-gradient: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        --card-bg: #ffffff;
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --border-color: #d1d5db;
        --input-bg: #f9fafb;
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    [data-theme="dark"] {
        --bg-gradient: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        --card-bg: #1f2937;
        --text-main: #f9fafb;
        --text-muted: #9ca3af;
        --border-color: #374151;
        --input-bg: #374151;
        --primary: #6366f1;
        --primary-hover: #4f46e5;
        --shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.3);
    }

    body {
        background: var(--bg-gradient);
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        margin: 0;
        padding: 2rem 0;
        transition: background 0.5s ease, color 0.3s ease;
    }

    /* Carte animée */
    .animated-card {
        background: var(--card-bg);
        max-width: 600px;
        margin: 2rem auto;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: var(--shadow);
        border-top: 5px solid var(--primary);
        animation: fadeIn 0.6s ease-in-out;
        transition: background 0.5s ease, box-shadow 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animated-card h2 {
        font-size: 1.75rem;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        position: relative;
    }

    /* Ligne décorative animée */
    .animated-card h2::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40px;
        height: 4px;
        background-color: var(--primary);
        border-radius: 2px;
        transition: width 0.3s;
    }
    .animated-card:hover h2::after { width: 80px; }

    /* Formulaire */
    .form-group {
        margin-bottom: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .form-group input {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        outline: none;
        background-color: var(--input-bg);
        color: var(--text-main);
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
    }
    .form-group input:focus + label { color: var(--primary); }
    .form-group input:disabled { background-color: #cbd5e1; cursor: not-allowed; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 640px) {
        .form-grid { grid-template-columns: repeat(2, 1fr); }
        .form-full { grid-column: span 2; }
    }

    /* Boutons */
    .mt {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.85rem 1.5rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s ease;
    }

    .btn-primary { background-color: var(--primary); color: #ffffff; }
    .btn-primary:hover { background-color: var(--primary-hover); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }

    .btn-secondary { background-color: #9ca3af; color: #ffffff; }
    .btn-secondary:hover { background-color: #6b7280; box-shadow: 0 4px 12px rgba(156, 163, 175, 0.3); }

    .btn:active { transform: translateY(2px); }
</style>

<div class="animated-card">
    <h2>Modifier le lieu</h2>
    <form action="update.php" method="POST">
        <input type="hidden" name="idlieu" value="<?= htmlspecialchars($lieu['idlieu']) ?>">
        
        <div class="form-grid">
            <div class="form-group">
                <label>Code lieu</label>
                <input type="text" value="<?= htmlspecialchars($lieu['idlieu']) ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" maxlength="100" required
                       value="<?= htmlspecialchars($lieu['province']) ?>">
            </div>
            
            <div class="form-group form-full">
                <label for="design">Désignation</label>
                <input type="text" id="design" name="design" maxlength="100" required
                       value="<?= htmlspecialchars($lieu['design']) ?>">
            </div>
        </div>
        
        <div class="mt">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
</script>

<?php require_once '../../includes/footer.php'; ?>