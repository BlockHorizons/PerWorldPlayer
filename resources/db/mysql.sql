-- #!mysql
-- #{ perworldplayer

-- #  { init
-- #    { data
CREATE TABLE IF NOT EXISTS data
(
    id              VARBINARY(96)    NOT NULL PRIMARY KEY,
    armor_inventory BLOB             NOT NULL,
    inventory       BLOB             NOT NULL,
    ender_inventory BLOB             NOT NULL,
    health          FLOAT UNSIGNED   NOT NULL,
    effects         BLOB             NOT NULL,
    gamemode        TINYINT UNSIGNED NOT NULL,
    experience      INT UNSIGNED     NOT NULL,
    food            FLOAT UNSIGNED   NOT NULL,
    exhaustion      FLOAT UNSIGNED   NOT NULL,
    saturation      FLOAT UNSIGNED   NOT NULL
);
-- #    }
-- #  }

-- #  { load
-- #    { data
-- #      :id string
SELECT armor_inventory AS armor_inventory,
       inventory       AS inventory,
       ender_inventory AS ender_inventory,
       health,
       effects,
       gamemode,
       experience,
       food,
       exhaustion,
       saturation
FROM data
WHERE id = :id;
-- #    }
-- #  }

-- #  { save
-- #    { data
-- #      :id string
-- #      :armor_inventory string
-- #      :inventory string
-- #      :ender_inventory string
-- #      :health float
-- #      :effects string
-- #      :gamemode int
-- #      :experience int
-- #      :food float
-- #      :exhaustion float
-- #      :saturation float
INSERT INTO data(id, armor_inventory, inventory, ender_inventory, health, effects, gamemode, experience, food, exhaustion, saturation)
VALUES (:id, :armor_inventory, :inventory, :ender_inventory, :health, :effects, :gamemode, :experience, :food, :exhaustion, :saturation)
ON DUPLICATE KEY UPDATE
    armor_inventory=VALUES(armor_inventory),
    inventory=VALUES(inventory),
    ender_inventory=VALUES(ender_inventory),
    health=VALUES(health),
    effects=VALUES(effects),
    gamemode=VALUES(gamemode),
    experience=VALUES(experience),
    food=VALUES(food),
    exhaustion=VALUES(exhaustion),
    saturation=VALUES(saturation);
-- #    }
-- #  }

-- #}