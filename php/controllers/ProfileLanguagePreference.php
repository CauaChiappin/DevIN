<?php

declare(strict_types=1);

class ProfileLanguagePreference
{
    private const DEFAULT_LANGUAGE = 'pt-BR';
    private const SUPPORTED_LANGUAGES = ['pt-BR', 'en', 'es'];
    private const PROFILE_TYPES = ['pessoa', 'empresa', 'adm'];

    public static function load(mysqli $conn, string $tipo, int $id): string
    {
        self::validateProfile($tipo, $id);

        foreach (['preferencias_dashboard', 'preferencias_perfil'] as $tableName) {
            try {
                $stmt = $conn->prepare(
                    'SELECT idioma FROM ' . $tableName . ' WHERE ' . (
                        $tableName === 'preferencias_dashboard'
                            ? 'perfil_tipo = ? AND perfil_id = ?'
                            : 'tipo_perfil = ? AND perfil_id = ?'
                    ) . ' LIMIT 1'
                );
                $stmt->bind_param('si', $tipo, $id);
                $stmt->execute();
                $preference = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $language = $preference['idioma'] ?? self::DEFAULT_LANGUAGE;
                return in_array($language, self::SUPPORTED_LANGUAGES, true)
                    ? $language
                    : self::DEFAULT_LANGUAGE;
            } catch (mysqli_sql_exception $exception) {
                if ((int) $exception->getCode() !== 1146) {
                    throw $exception;
                }
            }
        }

        return self::DEFAULT_LANGUAGE;
    }

    public static function save(mysqli $conn, string $tipo, int $id, string $idioma): void
    {
        self::validateProfile($tipo, $id);
        if (!in_array($idioma, self::SUPPORTED_LANGUAGES, true)) {
            throw new InvalidArgumentException('Idioma inválido.');
        }

        foreach (['preferencias_dashboard', 'preferencias_perfil'] as $tableName) {
            $conn->query(
                $tableName === 'preferencias_dashboard'
                    ? 'CREATE TABLE IF NOT EXISTS preferencias_dashboard (
                        perfil_tipo VARCHAR(16) NOT NULL,
                        perfil_id INT UNSIGNED NOT NULL,
                        idioma VARCHAR(5) NOT NULL DEFAULT \'pt-BR\',
                        atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (perfil_tipo, perfil_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                    : 'CREATE TABLE IF NOT EXISTS preferencias_perfil (
                        tipo_perfil VARCHAR(20) NOT NULL,
                        perfil_id INT NOT NULL,
                        idioma VARCHAR(10) NOT NULL DEFAULT \'pt-BR\',
                        PRIMARY KEY (tipo_perfil, perfil_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $stmt = $conn->prepare(
                $tableName === 'preferencias_dashboard'
                    ? 'INSERT INTO preferencias_dashboard (perfil_tipo, perfil_id, idioma)
                       VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE idioma = VALUES(idioma)'
                    : 'INSERT INTO preferencias_perfil (tipo_perfil, perfil_id, idioma)
                       VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE idioma = VALUES(idioma)'
            );
            $stmt->bind_param('sis', $tipo, $id, $idioma);
            $stmt->execute();
            $stmt->close();
        }
    }

    private static function validateProfile(string $tipo, int $id): void
    {
        if (!in_array($tipo, self::PROFILE_TYPES, true) || $id <= 0) {
            throw new InvalidArgumentException('Perfil inválido.');
        }
    }
}
