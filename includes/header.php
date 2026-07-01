<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Affectations</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        :root {
            --electric-green: #A3FF12;    /* Vert électrique éclatant */
            --electric-gray:  rgba(20, 22, 32, 0.9); /* Gris électrique métallisé transparent */
            --text-main:      #f0f4ff;
            --text-muted:     #8892b0;
            --nav-glow:       0 0 15px rgba(163, 255, 18, 0.35), 0 0 30px rgba(163, 255, 18, 0.15);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #03030a;
        }

        /* ══ NAVBAR COMPONENT ══ */
        .navbar {
            background: var(--electric-gray);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            
            /* Bordure en vert électrique */
            border-bottom: 2px solid var(--electric-green);
            /* Effet de lueur (Glow) sur la bordure */
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), var(--nav-glow);
            
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9rem 3rem;
            position: sticky;
            top: 0;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        /* Logo / Marque */
        .navbar .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navbar .brand::before {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            background: var(--electric-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--electric-green);
        }

        /* Menu de navigation */
        .navbar ul {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .navbar ul li {
            position: relative;
        }

        /* Liens de navigation */
        .navbar ul li a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0.5rem 0.2rem;
            transition: color 0.3s ease, text-shadow 0.3s ease;
            position: relative;
        }

        /* Effet au survol des liens (Texte passe au vert électrique) */
        .navbar ul li a:hover {
            color: var(--electric-green);
            text-shadow: 0 0 8px rgba(163, 255, 18, 0.6);
        }

        /* Ligne animée sous les liens au survol */
        .navbar ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--electric-green);
            box-shadow: 0 0 8px var(--electric-green);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: translateX(-50%);
        }

        .navbar ul li a:hover::after {
            width: 100%;
        }

        /* Container principal */
        .container {
            max-width: 1300px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <span class="brand">Gestion Affectations</span>
    <ul>
        <li><a href="/pages/lieu/index.php">Lieux</a></li>
        <li><a href="/pages/employe/index.php">Employés</a></li>
        <li><a href="/pages/affecter/index.php">Affecter</a></li>
        <li><a href="/pages/historique/index.php">Historique</a></li>
        <li><a href="/pages/affectation/rapport.php">Rapport</a></li>
        <li><a href="/pages/affectation/non_affectes.php">Non affectés</a></li>
    </ul>
</nav>

<main class="container">