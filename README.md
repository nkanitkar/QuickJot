QuickJot
=========================================
Written by Nishant Kanitkar

This is a quickly hacked up permanent notepad application. 
I wrote it after getting frustrated with losing my (unsaved) notes. <br>
I use it personally as a development log.

The application will save all changes to a central database, as you type them. <br>
It also allows you to quickly email the contents of the file to predefinied recipients.

REQUIREMENTS
--------------
- PHP
- MySQL Database

Installation Configuration Steps
--------------------------------------------
- DATABASE CREDENTIALS
- CONSTANTS
- FORM CHECKBOXES
- EMAIL MAPPINGS

Important functions
-------------------
	connectDB()
		function that opens a mysql database connection using mysql_connect()
		change username, password, database, and hostname as required.
	
	sendemail($to,$subject,$body,$headers)
		sends a email with the provided headers.
		mailserver settings are your problem
	
	See "EMAIL MAPPINGS" and "FORM CHECKBOXES" in the code to add email recipients.


Notes
--------
	Passwords are implemented as a Salted SHA1 hash:
	SHA1(PASSWORD+SALT)
	
	You will have to add usernames and password hashes into the database manually.

MySQL Database Schema
--------------------
```
	
	CREATE TABLE IF NOT EXISTS `notepad` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user` varchar(100) NOT NULL,
			`title` varchar(255) NOT NULL,
			`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`to` text NOT NULL COMMENT 'csv of people message was sent to',
			`body` text NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=198 ;

	CREATE TABLE IF NOT EXISTS `users` (
			`user` varchar(32) NOT NULL,
			`pass` text NOT NULL,
			`note` text NOT NULL,
			PRIMARY KEY (`user`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;

```
