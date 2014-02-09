<?php

/*
 
PHP unit thinks this is code without a commenting out.
One-To-One, Unidirectional¶

CREATE TABLE Product (
id INT AUTO_INCREMENT NOT NULL,
shipping_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE Shipping (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

ALTER TABLE Product ADD FOREIGN KEY (shipping_id) REFERENCES Shipping(id);



One-To-One, Bidirectional¶

CREATE TABLE Cart (
id INT AUTO_INCREMENT NOT NULL,
customer_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE Customer (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

ALTER TABLE Cart ADD FOREIGN KEY (customer_id) REFERENCES Customer(id);


One-To-One, Self-referencing¶


CREATE TABLE Student (
id INT AUTO_INCREMENT NOT NULL,
mentor_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
ALTER TABLE Student ADD FOREIGN KEY (mentor_id) REFERENCES Student(id);



One-To-Many, Unidirectional with Join Table

CREATE TABLE User (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE users_phonenumbers (
user_id INT NOT NULL,
phonenumber_id INT NOT NULL,
UNIQUE INDEX users_phonenumbers_phonenumber_id_uniq (phonenumber_id),
PRIMARY KEY(user_id, phonenumber_id)
) ENGINE = InnoDB;

CREATE TABLE Phonenumber (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

ALTER TABLE users_phonenumbers ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE users_phonenumbers ADD FOREIGN KEY (phonenumber_id) REFERENCES Phonenumber(id);


Many-To-One, Unidirectional¶

CREATE TABLE User (
id INT AUTO_INCREMENT NOT NULL,
address_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE Address (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;

ALTER TABLE User ADD FOREIGN KEY (address_id) REFERENCES Address(id);



One-To-Many, Bidirectional¶


CREATE TABLE Product (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
CREATE TABLE Feature (
id INT AUTO_INCREMENT NOT NULL,
product_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
ALTER TABLE Feature ADD FOREIGN KEY (product_id) REFERENCES Product(id);



One-To-Many, Self-referencing¶


CREATE TABLE Category (
id INT AUTO_INCREMENT NOT NULL,
parent_id INT DEFAULT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
ALTER TABLE Category ADD FOREIGN KEY (parent_id) REFERENCES Category(id);


Many-To-Many, Unidirectional¶

CREATE TABLE User (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
CREATE TABLE users_groups (
user_id INT NOT NULL,
group_id INT NOT NULL,
PRIMARY KEY(user_id, group_id)
) ENGINE = InnoDB;
CREATE TABLE Group (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
ALTER TABLE users_groups ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE users_groups ADD FOREIGN KEY (group_id) REFERENCES Group(id);


Many-To-Many, Bidirectional¶
See above

Many-To-Many, Self-referencing¶
CREATE TABLE User (
id INT AUTO_INCREMENT NOT NULL,
PRIMARY KEY(id)
) ENGINE = InnoDB;
CREATE TABLE friends (
user_id INT NOT NULL,
friend_user_id INT NOT NULL,
PRIMARY KEY(user_id, friend_user_id)
) ENGINE = InnoDB;
ALTER TABLE friends ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE friends ADD FOREIGN KEY (friend_user_id) REFERENCES User(id);













## One to one - unidirectional

Surprisingly 'email' is the 'owning' side, as it owns the foreign key

CREATE TABLE `user` (
`userID` bigint(20) NOT NULL AUTO_INCREMENT,
`firstName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
`lastName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
delimiter ;

CREATE TABLE `email` (
`emailID` bigint(20) NOT NULL AUTO_INCREMENT,
`address` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
`userID` bigint(20) NOT NULL,
PRIMARY KEY (`emailID`),
KEY `fk_email_user_userID` (`userID`),
CONSTRAINT `fk_email_user_userID` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



## One to one - Bidirectional with join

CREATE TABLE `user` (
  `userID` bigint(20) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
delimiter ;

CREATE TABLE `email` (
  `emailID` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`emailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emailuser` (
`emailID` bigint(20) NOT NULL,
`userID` bigint(20) NOT NULL,
KEY `fk_emailuser_email_emailID` (`emailID`),
KEY `fk_emailuser_user_userID` (`userID`),
CONSTRAINT `fk_emailuser_user_userID` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `fk_emailuser_email_emailID` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


## One to one - ONE_TO_ONE_SELF_REFERENCING

CREATE TABLE `user` (
`userID` bigint(20) NOT NULL AUTO_INCREMENT,
`firstName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
`lastName` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
`referrerUserID` bigint(20) NOT NULL,
PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
delimiter ;


*/