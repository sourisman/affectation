<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Setting extends Model
{
    protected string $table = 'settings';

    protected string $primaryKey = 'setting_key';

    protected array $fillable = ['setting_key', 'setting_value', 'group_name', 'created_at', 'updated_at'];

    /** @var array<string, array<string, string>>|null */
    private static ?array $cache = null;

    /** Toutes les valeurs, groupées par section de configuration. */
    /** @return array<string, array<string, string>> */
    public function groups(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $cache = ['general' => [], 'seo' => [], 'contact' => [], 'features' => []];

        try {
            foreach (Database::select('SELECT setting_key, setting_value, group_name FROM settings') as $row) {
                $cache[(string) $row['group_name']][(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
            }
        } catch (\Throwable) {
            // Base non migrée : on retombe sur les valeurs par défaut.
        }

        return self::$cache = $cache;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        foreach ($this->groups() as $group) {
            if (array_key_exists($key, $group)) {
                return $group[$key];
            }
        }

        return $default;
    }

    public function put(string $key, string $value, string $group = 'general'): void
    {
        $exists = (int) Database::scalar(
            'SELECT COUNT(*) FROM settings WHERE setting_key = :key',
            ['key' => $key],
        ) > 0;

        if ($exists) {
            Database::statement(
                'UPDATE settings SET setting_value = :value, group_name = :group, updated_at = :now
                 WHERE setting_key = :key',
                ['value' => $value, 'group' => $group, 'now' => date('Y-m-d H:i:s'), 'key' => $key],
            );
        } else {
            Database::statement(
                'INSERT INTO settings (setting_key, setting_value, group_name, created_at, updated_at)
                 VALUES (:key, :value, :group, :now, :now)',
                ['key' => $key, 'value' => $value, 'group' => $group, 'now' => date('Y-m-d H:i:s')],
            );
        }

        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
