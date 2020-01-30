-- #!sqlite
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
SELECT HEX(armor_inventory) as armor_inventory,
       HEX(inventory)       AS inventory,
       HEX(ender_inventory) AS ender_inventory,
       health,
       effects,
       gamemode,
       experience,
       food,
       exhaustion,
       saturation
FROM data
WHERE id = X:id;
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
INSERT OR
REPLACE INTO data(id, armor_inventory, inventory, ender_inventory, health, effects, gamemode, experience, food,
                  exhaustion, saturation)
VALUES (X:id, X:armor_inventory, X:inventory, X:ender_inventory, :health, :effects, :gamemode, :experience, :food,
        :exhaustion, :saturation);
-- #    }
-- #  }

-- #}