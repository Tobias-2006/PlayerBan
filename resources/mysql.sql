-- #! mysql
-- #{ playerban
-- #    { init
-- #        { bans
CREATE TABLE IF NOT EXISTS bans(
    id INT AUTO_INCREMENT,
    target VARCHAR(32) NOT NULL,
    moderator VARCHAR(32) NOT NULL,
    expiry_time INT NOT NULL,
    pun_id INT NOT NULL,
    creation_time INT NOT NULL,
    PRIMARY KEY(id)
);
-- #        }
-- #        { punishments
CREATE TABLE IF NOT EXISTS punishments(
    id INT NOT NULL,
    duration INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
-- #        }
-- #        { logs
CREATE TABLE IF NOT EXISTS logs(
    type INT NOT NULL,
    description TEXT NOT NULL,
    moderator VARCHAR(32) NOT NULL,
    target VARCHAR(32),
    creation_time INT NOT NULL
);
-- #        }
-- #    }
-- #    { log
-- #        { save
-- #          :type int
-- #          :description string
-- #          :moderator string
-- #          :target string
-- #          :creation int
INSERT INTO logs(
    type,
    description,
    moderator,
    target,
    creation_time
) VALUES(
    :type,
    :description,
    :moderator,
    :target,
    :creation
);
-- #        }
-- #        { delete
-- #          :moderator string
-- #          :creation int
DELETE FROM logs WHERE moderator = :moderator AND creation_time = :creation;
-- #        }
-- #        { get.page
-- #          :page int
-- #          :limit int
SELECT * FROM logs ORDER BY creation_time DESC LIMIT :page, :limit;
-- #        }
-- #        { get.logcount
SELECT COUNT(*) FROM logs;
-- #        }
-- #    }
-- #    { punishment
-- #        { get
-- #          :id int
SELECT * FROM punishments WHERE id = :id LIMIT 1;
-- #        }
-- #        { get.all
SELECT * FROM punishments;
-- #        }
-- #        { save
-- #          :id int
-- #          :duration int
-- #          :description string
INSERT INTO punishments(
    id,
    duration,
    description
) VALUES(
    :id,
    :duration,
    :description
);
-- #        }
-- #        { delete
-- #          :id int
DELETE FROM punishments WHERE id = :id;
-- #        }
-- #        { update
-- #          :duration int
-- #          :description string
-- #          :id int
UPDATE punishments SET duration = :duration, description = :description WHERE id = :id;
-- #        }
-- #    }
-- #    { ban
-- #        { get
-- #          :target string
-- #          :timestamp int
SELECT * FROM bans WHERE target = :target AND expiry_time > :timestamp LIMIT 1;
-- #        }
-- #        { save
-- #          :target string
-- #          :moderator string
-- #          :expiry int
-- #          :punId int
-- #          :creation int
INSERT INTO bans(
    target,
    moderator,
    expiry_time,
    pun_id,
    creation_time
) VALUES(
    :target,
    :moderator,
    :expiry,
    :punId,
    :creation
);
-- #        }
-- #        { remove
-- #          :timestamp int
-- #          :target string
UPDATE bans SET expiry_time = :timestamp WHERE target = :target AND expiry_time > :timestamp;
-- #        }
-- #        { get.banhistory
-- #          :target string
SELECT * FROM bans WHERE target = :target ORDER BY creation_time DESC;
-- #        }
-- #        { get.currentbans
-- #          :timestamp int
-- #          :page int
-- #          :limit int
SELECT * FROM bans WHERE expiry_time > :timestamp ORDER BY creation_time DESC LIMIT :page, :limit;
-- #        }
-- #        { get.currentbans.count
-- #          :timestamp int
SELECT COUNT(*) FROM bans WHERE expiry_time > :timestamp;
-- #        }
-- #    }
-- #}
