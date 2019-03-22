# Online Banking
Online banking system for use in Project 3/4.

## How to build

### Create the database `clients`
```sql
  CREATE TABLE  `users` (
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
    `username` VARCHAR( 50 ) NOT NULL UNIQUE ,
    `password` VARCHAR( 255 ) NOT NULL ,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP ,
    PRIMARY KEY (`id`)
  )
  ENGINE = INNODB
  CHARSET = utf8
  COLLATE utf8_bin
  COMMENT =  'This table is meant for the client information.'
```

### Create the database `accounts`
```sql
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `iban` varchar(14) NOT NULL,
  `nuid` VARCHAR(8) NOT NULL ,
  `balance` int(11) NOT NULL,
  `pin_attempts` int(1) NOT NULL,
  `pin` varchar(4) NOT NULL,
   PRIMARY KEY (iban),
   FOREIGN KEY (id)
   REFERENCES users (id),
   UNIQUE (`nuid`)
)

ENGINE = InnoDB
CHARSET=utf8
COLLATE utf8_bin
COMMENT = 'This table is meant to keep track of all the bankaccounts.';
```

### Create the database `transactions`
```sql
CREATE TABLE  `transactions` (
  `transaction_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
  `iban_sender` VARCHAR(14) NOT NULL ,
  `iban_recipient` VARCHAR(14) NULL ,
  `amount` int(11) NULL ,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`transaction_id`),
)
ENGINE = INNODB
CHARSET = utf8
COLLATE utf8_bin
COMMENT =  'This table is meant to keep track of al the transactions.'
```
