<?php

declare(strict_types=1);

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    require_once(__DIR__ . '../../../config/database.mysql.php');

    $pdo = new PDO(CONFIG_DB_MYSQL_DSN, CONFIG_DB_MYSQL_USERNAME, CONFIG_DB_MYSQL_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
        Pdo\Mysql::ATTR_USE_BUFFERED_QUERY => 1,
    ]);

    return $pdo;
}

function db_test(): bool
{
    $st = pdo()->prepare('select NOW()');
    $st->execute();
    return $st->rowCount() >= 1;
}

function db_exec(string $sql, array $params = []): int
{
    $st = pdo()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}

function db_all(string $sql, array $params = []): array
{
    $st = pdo()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $st = pdo()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    $st->closeCursor();
    return ($row === false) ? null : $row;
}

function ensure_schema(): void
{
    // skip migrations if schema found
    try {
        $res = pdo()->query('select 1 from migrations');
        $res->closeCursor();
    } catch (Exception $e) {
        $res = false;
    }

    if ($res !== false) return;

    // starting schema installation:

    // accounts
    pdo()->exec(
        '
            CREATE TABLE IF NOT EXISTS `accounts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
                `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
                `purpose` smallint(6) DEFAULT NULL,
                `loggined_at` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );

    // create admin user
    pdo()->exec(
        '
            INSERT INTO accounts (`username`, `password`)
            VALUES ( "admin@ex.com", "' . pass_hash('admin') . '")
        ',
    );

    // links (shortens)
    pdo()->exec(
        '
            CREATE TABLE IF NOT EXISTS `links` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `shortenid` varchar(18) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
                `destination` varchar(255) NOT NULL,
                `secret` varchar(32) NOT NULL,
                `owner` int(11) DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );

    // payments
    pdo()->exec(
        '
            CREATE TABLE `payments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `owner` int(11) NOT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `owner` (`owner`),
                CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );

    // products
    pdo()->exec(
        '
            CREATE TABLE `products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` varchar(255) NOT NULL,
                `price` int(11) NOT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );

    // create test products
    pdo()->exec(
        '
            INSERT INTO products (`name`, `description`, `price`)
            VALUES ("Free Source", "", "1");
            INSERT INTO products (`name`, `description`, `price`)
            VALUES ("Team", "", "2");
            INSERT INTO products (`name`, `description`, `price`)
            VALUES ("Team Pro", "", "3");
            INSERT INTO products (`name`, `description`, `price`)
            VALUES ("Enterprise", "", "4");
        ',
    );

    // orders
    pdo()->exec(
        '
            CREATE TABLE `orders` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `owner` int(11) NOT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `owner` (`owner`),
                CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );

    // analytics
    pdo()->exec(
        '
            CREATE TABLE `analytics` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `linkid` int(11) NOT NULL,
                `ip` varchar(16) DEFAULT NULL,
                `geoip_cntr` varchar(32) DEFAULT NULL,
                `geoip_city` varchar(32) DEFAULT NULL,
                `http_referer` varchar(16) DEFAULT NULL,
                `http_origin` varchar(16) DEFAULT NULL,
                `chua_ismobile` int(1) DEFAULT NULL,
                `chua_arch` varchar(12) DEFAULT NULL,
                `chua_model` varchar(32) DEFAULT NULL,
                `chua_platform` varchar(32) DEFAULT NULL,
                `chua_platformver` varchar(32) DEFAULT NULL,
                `chua_viewport_w` varchar(32) DEFAULT NULL,
                `chua_viewport_h` varchar(32) DEFAULT NULL,
                `ua_platform` varchar(32) DEFAULT NULL,
                `ua_arch` varchar(32) DEFAULT NULL,
                `ua_os` varchar(30) DEFAULT NULL,
                `ua_osver` varchar(30) DEFAULT NULL,
                `ua_browser` varchar(32) DEFAULT NULL,
                `ua_browserver` varchar(32) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `linkid` (`linkid`),
                CONSTRAINT `analytics_ibfk_1` FOREIGN KEY (`linkid`) REFERENCES `links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        '
    );

    // dummy table for migrations initialization check
    // !! should be LAST!!
    pdo()->exec(
        '
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `is_applied` smallint(6) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci
        ',
    );
}
                // KEY `owner` (`owner`),
                // CONSTRAINT `links_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`)