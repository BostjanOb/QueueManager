CREATE TABLE IF NOT EXISTS tasks (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR NOT NULL ,
  `params` TEXT NULL ,
  `result` TEXT NULL ,
  `started_at` INT NULL ,
  `completed_at` INT NULL ,
  `status` VARCHAR NOT NULL DEFAULT 'QUEUED' ,
  PRIMARY KEY (`id`)
);