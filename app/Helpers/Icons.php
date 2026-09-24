<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Bibliothèque d'icônes SVG en ligne.
 *
 * Les tracés sont intégrés au HTML (aucune requête réseau supplémentaire),
 * avec un rendu homogène : grille 24×24, contour de 1.8, extrémités arrondies.
 */
final class Icons
{
    /**
     * Tracés partagés : chaque entrée contient le contenu d'un <svg> 24×24.
     *
     * @var array<string, string>
     */
    private const PATHS = [
        // Interface & navigation
        'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left'    => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'arrow-up'      => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'plus'          => '<path d="M12 5v14M5 12h14"/>',
        'minus'         => '<path d="M5 12h14"/>',
        'menu'          => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'grid'          => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',
        'layers'        => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'cursor'        => '<path d="m4 4 7.5 16 2-6.5L20 11.5 4 4Z"/>',
        'search'        => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'filter'        => '<path d="M3 5h18l-7 8v6l-4-2v-4L3 5Z"/>',
        'external'      => '<path d="M14 4h6v6"/><path d="M20 4l-8.5 8.5"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'copy'          => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a1 1 0 0 1 1-1h9"/>',
        'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1v.2a2 2 0 1 1-4 0v-.2a1.6 1.6 0 0 0-2.7-1.1l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 15a2 2 0 0 1 0-4h.2a1.6 1.6 0 0 0 1.1-2.7l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 9.8 4.6V4a2 2 0 1 1 4 0v.2a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 0 1 0 4h-.2a1.6 1.6 0 0 0-1.4 1.1Z"/>',
        'bell'          => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'download'      => '<path d="M12 3v12"/><path d="m7 11 5 5 5-5"/><path d="M5 21h14"/>',
        'upload'        => '<path d="M12 17V5"/><path d="m7 9 5-5 5 5"/><path d="M5 21h14"/>',
        'printer'       => '<path d="M7 9V3h10v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 15h10v6H7z"/>',
        'refresh'       => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/>',
        'key'           => '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.2-8.2 2 2-2 2 2 2-2.5 2.5-2-2-2.5 2.5"/>',
        'log-out'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',

        // Statuts
        'check'         => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle'  => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        'x'             => '<path d="M18 6 6 18M6 6l12 12"/>',
        'x-circle'      => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
        'alert'         => '<path d="M10.3 3.9 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17v.5"/>',
        'info'          => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.5"/>',
        'eye'           => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'       => '<path d="M10.6 6.2A9.9 9.9 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.6 3.4"/><path d="M6.3 7.8A16.6 16.6 0 0 0 2.5 12s3.5 6 9.5 6a9.6 9.6 0 0 0 4-.8"/><path d="M3 3l18 18"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',

        // Thème
        'sun'           => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.8 4.8l1.8 1.8M17.4 17.4l1.8 1.8M19.2 4.8l-1.8 1.8M6.6 17.4l-1.8 1.8"/>',
        'moon'          => '<path d="M20 14.2A8.5 8.5 0 0 1 9.8 4 8.5 8.5 0 1 0 20 14.2Z"/>',

        // Métier
        'users'         => '<path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7.5" r="3.5"/><path d="M21 20v-1.5a4 4 0 0 0-3-3.9"/><path d="M15.5 4.2a3.5 3.5 0 0 1 0 6.6"/>',
        'user'          => '<circle cx="12" cy="8" r="3.6"/><path d="M5 20v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1"/>',
        'user-plus'     => '<circle cx="10" cy="8" r="3.6"/><path d="M3 20v-1a5 5 0 0 1 5-5h3a5 5 0 0 1 3 1"/><path d="M18 10v6M15 13h6"/>',
        'building'      => '<path d="M4 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16"/><path d="M14 10h5a1 1 0 0 1 1 1v10"/><path d="M7 8h3M7 12h3M7 16h3M17 14h1M17 18h1"/><path d="M2 21h20"/>',
        'briefcase'     => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>',
        'route'         => '<circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="6" r="2.5"/><path d="M8.5 18H14a3.5 3.5 0 0 0 0-7H9a3.5 3.5 0 0 1 0-7h3.5"/>',
        'shuffle'       => '<path d="M17 4h4v4"/><path d="M21 4l-6.5 6.5"/><path d="M17 20h4v-4"/><path d="M21 20l-6.5-6.5"/><path d="M3 20l6-6"/><path d="M3 4l5 5"/>',
        'history'       => '<path d="M3.5 12a8.5 8.5 0 1 0 2.8-6.3"/><path d="M3 4v4h4"/><path d="M12 8v4.5l3 1.8"/>',
        'calendar'      => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'clipboard'     => '<rect x="6" y="4" width="12" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9.5 11h5M9.5 15h3"/>',
        'chart'         => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'trend-up'      => '<path d="m3 17 5.5-5.5 3.5 3.5L21 6"/><path d="M15 6h6v6"/>',
        'trend-down'    => '<path d="m3 7 5.5 5.5L12 9l9 9"/><path d="M15 18h6v-6"/>',
        'target'        => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/>',
        'gauge'         => '<path d="M3.5 18a9 9 0 1 1 17 0"/><path d="m12 14 4-3.5"/><circle cx="12" cy="14" r="1.4"/>',
        'activity'      => '<path d="M3 12h4l2.5-7 4 14 2.5-7h5"/>',
        'bolt'          => '<path d="M13.5 2 5 13h5l-.5 9L19 11h-5.5l.5-9Z"/>',
        'shield'        => '<path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'lock'          => '<rect x="4.5" y="10.5" width="15" height="10.5" rx="2"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/>',
        'sparkles'      => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8L12 3Z"/><path d="M19 15l.9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9L19 15Z"/>',
        'cpu'           => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M10 2v3M14 2v3M10 19v3M14 19v3M2 10h3M2 14h3M19 10h3M19 14h3"/>',
        'database'      => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        'code'          => '<path d="m9 8-4.5 4L9 16"/><path d="m15 8 4.5 4L15 16"/>',
        'plug'          => '<path d="M9 3v6M15 3v6"/><path d="M7 9h10v2.5a5 5 0 0 1-5 5 5 5 0 0 1-5-5V9Z"/><path d="M12 16.5V21"/>',
        'inbox'         => '<path d="M3 12h5l1.5 3h5L16 12h5"/><path d="M4.5 5h15l1.5 7v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5l1.5-7Z"/>',
        'mail'          => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
        'phone'         => '<path d="M5 3h3.5l1.5 4.5-2 1.5a12 12 0 0 0 6 6l1.5-2 4.5 1.5V18a3 3 0 0 1-3 3A15.5 15.5 0 0 1 2 6a3 3 0 0 1 3-3Z"/>',
        'map-pin'       => '<path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'file'          => '<path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7l-4-4Z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 17h4"/>',
        'star'          => '<path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8L3.5 9.7l5.9-.9L12 3.5Z"/>',
        'quote'         => '<path d="M9 6c-3 1.3-4.5 3.6-4.5 7V18h5v-5H6.5c0-2 .8-3.4 2.5-4.3V6Z"/><path d="M18 6c-3 1.3-4.5 3.6-4.5 7V18h5v-5h-3c0-2 .8-3.4 2.5-4.3V6Z"/>',
        'trash'         => '<path d="M4 7h16"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M10 11v6M14 11v6"/>',
        'edit'          => '<path d="M4 20h4l11-11-4-4L4 16v4Z"/><path d="m14.5 5.5 4 4"/>',
        'home'          => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1v-9.5Z"/>',
        'link'          => '<path d="M10 13.5a3.5 3.5 0 0 0 5 0l3-3a3.5 3.5 0 0 0-5-5l-1 1"/><path d="M14 10.5a3.5 3.5 0 0 0-5 0l-3 3a3.5 3.5 0 0 0 5 5l1-1"/>',

        // Réseaux sociaux (contour)
        'linkedin'      => '<path d="M5.5 8.5h3v10h-3z"/><circle cx="7" cy="5.5" r="1.6"/><path d="M11 18.5v-5.2a3.2 3.2 0 0 1 6.4 0v5.2h-3v-4.7a1.4 1.4 0 0 0-2.4-1v5.7z"/>',
        'github'        => '<path d="M9.5 21v-2.6c-3 .6-3.6-1.4-3.6-1.4-.3-.9-.9-1.5-.9-1.5-.8-.5 0-.5 0-.5.9.1 1.3 1 1.3 1 .7 1.3 2 1 2.5.8.1-.6.3-1 .6-1.3-2.4-.3-4.2-1.2-4.2-4.5 0-1 .3-1.9.9-2.5-.1-.3-.4-1.3.1-2.6 0 0 .8-.2 2.6 1a6.9 6.9 0 0 1 3.6 0c1.8-1.2 2.6-1 2.6-1 .5 1.3.2 2.3.1 2.6.6.6.9 1.5.9 2.5 0 3.3-1.8 4.2-4.2 4.5.4.4.7 1 .7 2V21"/>',
        'x-social'      => '<path d="M4 4h4l4.5 6L17 4h3l-6.6 8.2L20.5 20h-4l-4.7-6.2L7 20H4l7-8.5L4 4Z"/>',
        'youtube'       => '<rect x="2.5" y="6" width="19" height="12" rx="4"/><path d="m11 9.5 5 2.5-5 2.5z"/>',
    ];

    /** Rend une icône en SVG accessible. */
    public static function render(string $name, int $size = 20, string $class = '', string $label = ''): string
    {
        $path = self::PATHS[$name] ?? self::PATHS['info'];

        $attributes = sprintf(
            'width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"',
            $size,
        );

        if ($class !== '') {
            $attributes .= ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"';
        }

        $role = $label !== ''
            ? ' role="img" aria-label="' . htmlspecialchars($label, ENT_QUOTES) . '"'
            : ' aria-hidden="true"';

        return '<svg ' . $attributes . $role . '>' . $path . '</svg>';
    }

    /** Liste des icônes disponibles (utile pour l'administration). */
    public static function available(): array
    {
        return array_keys(self::PATHS);
    }
}
