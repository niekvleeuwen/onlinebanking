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
  `location` VARCHAR(100) NULL ,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`transaction_id`)
)
ENGINE = INNODB
CHARSET = utf8
COLLATE utf8_bin
COMMENT =  'This table is meant to keep track of al the transactions.'
```

### Check saldo

This call requires the following:

1. `nuid` You can get the nuid from the card.
2. `pin` This should be 4 numbers.

#### Request

The request should be made to `checksaldo.php`.

#### Response

The API respones with a status and balance.

##### Success

When your call is succesful, the respone is like this:

```json
{
  status: "0"
  balance: 10
}
```

##### Error

If something goes wrong, status is set to `1`. `error` contains the error message.

```json
{
  status: "1"
  error : "A useful error message"
}
```

### Withdraw money

This call requires the following:

1. `nuid` You can get the nuid from the card.
2. `pin` This should be 4 numbers.
3. `amount` This amount will be withdrawn.

#### Request

The request should be made to `withdraw.php`.

#### Response

The result is the balance after the transfer and a saldo. If the status is `0` the call has succeeded.

##### Success

```json
{
  status: "0"
  balance: 10
}
```

##### Error

If something goes wrong, status is set to `1`. `error` contains the error message.

```json
{
  status: "0"
  error: "A useful error message"
}
```

### Transfer money

The API respones with a status and amount.

This call requires the following:

1. `nuid` You can get the nuid from the card.
2. `pin` This should be 4 numbers.
3. `amount` This amount will be withdrawn.
3. `iban` The IBAN of the recipient.

#### Request

The request should be made to `transfer.php`.

#### Response

The result is the balance after the transfer,a saldo and the IBAN of the recipient. If the status is `0` the call has succeeded.

##### Success

The response will contain a `balance` if your transfer succeeded.

```json
{
  status: "0"
  balance: 10
  iban: "The IBAN of the recipient"
}
```

##### Error

If something goes wrong, status is set to `1`. `error` contains the error message.

```json
{
  status: "0"
  error: "A useful error message"
}
```
