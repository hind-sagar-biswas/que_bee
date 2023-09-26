<?php

declare(strict_types=1);

use Hindbiswas\QueBee\Col;
use Hindbiswas\QueBee\Table;
use PHPUnit\Framework\TestCase;
use Hindbiswas\QueBee\Table\Values\FK;
use Hindbiswas\QueBee\Table\Values\DefaultVal;

final class FullTableTest extends TestCase
{
    public function test_basic_users_table_build()
    {
        $expected = "CREATE TABLE IF NOT EXISTS users (`id` INT(11) UNSIGNED NULL AUTO_INCREMENT, `username` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `password` VARCHAR(255) NOT NULL, `is_superadmin` INT(2) NOT NULL DEFAULT '0', `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIME, `update_time` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIME, CONSTRAINT users_PK PRIMARY KEY (id), CONSTRAINT username_UC UNIQUE (`username`), CONSTRAINT email_UC UNIQUE (`email`)) ENGINE = InnoDB;";

        $query = Table::create('users')->columns([
            'id' => Col::integer(11)->unsigned()->pk()->ai(),
            'username' => Col::varchar()->unique(),
            'email' => Col::varchar()->unique(),
            'password' => Col::varchar(),
            'is_superadmin' => Col::integer(2)->default('0'),
            'create_time' => Col::dateTime()->default(DefaultVal::CURRENT_TIME),
            'update_time' => Col::dateTime()->setOnUpdate()->default(DefaultVal::CURRENT_TIME),
        ])->build();

        $this->assertSame($expected, $query);
    }

    public function test_table_with_foreign_keys_build()
    {
        $expected = "CREATE TABLE IF NOT EXISTS tokens (`id` INT UNSIGNED NULL AUTO_INCREMENT, `selector` VARCHAR(255) NOT NULL, `hashed_validator` VARCHAR(255) NOT NULL, `user_id` INT(11) UNSIGNED NOT NULL, `expiry` DATETIME NOT NULL, CONSTRAINT tokens_PK PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE = InnoDB;";

        $user = Table::create('users')->columns([
            'id' => Col::integer(11)->unsigned()->pk()->ai(),
            'username' => Col::varchar()->unique(),
            'email' => Col::varchar()->unique(),
            'password' => Col::varchar(),
            'is_superadmin' => Col::integer(2)->default('0'),
            'create_time' => Col::dateTime()->default(DefaultVal::CURRENT_TIME),
            'update_time' => Col::dateTime()->setOnUpdate()->default(DefaultVal::CURRENT_TIME),
        ]);

        $table = Table::create('tokens')->columns([
            'id' => Col::integer()->unsigned()->pk()->ai(),
            'selector' => Col::varchar(),
            'hashed_validator' => Col::varchar(),
            'user_id' => Col::integer(11)->unsigned(),
            'expiry' => Col::dateTime(),
        ])->foreign('user_id')->onDelete(FK::CASCADE)->reference($user, 'id');

        $query = $table->build();

        $this->assertSame($expected, $query);
    }
}
