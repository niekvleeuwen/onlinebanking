# Online Banking
Online banking system for use in Project 3/4.

## How to build

### Create the database `clients`
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
  PRIMARY KEY (`id`),
)

ENGINE = InnoDB
CHARSET=utf8
COLLATE utf8_bin
COMMENT = 'This table is meant for the client information.';
```

### Create the database `accounts`
```sql
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `iban` varchar(14) NOT NULL,
  `nuid` VARCHAR(8) NOT NULL ,
  `balance` int(11) NOT NULL,
  `pin_attempts` int(11) NOT NULL,
  `pin` varchar(32) NOT NULL,
   PRIMARY KEY (iban),
   FOREIGN KEY (id)
   REFERENCES users (id));

   UNIQUE (`nuid`)
)

ENGINE = InnoDB
CHARSET=utf8
COLLATE utf8_bin
COMMENT = 'This table is meant to keep track of all the bankaccounts.';
```
