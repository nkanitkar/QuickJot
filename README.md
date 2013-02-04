Written by Nishant Kanitkar
This is a quickly hacked up permanent notepad application. 
I wrote it after getting frustrated with losing my (unsaved) notes.
I use it personally as a development log.

The application will save all changes to a central database, as you type them. 
This way no changes will be lost. 
It also allows you to quickly email the contents of the file to predefinied recipients.

REQUIREMENTS:
PHP
MySQL Database

Variables to change per installation include: 
DATABASE CREDENTIALS
CONSTANTS
FORM CHECKBOXES
EMAIL MAPPINGS


-connectDB(), function that opens a mysql database connection using mysql_connect()
	--change username, password, database, and hostname as required.

	-sendemail($to,$subject,$body,$headers), sends a email with the provided headers.
	--mailserver settings are your problem


	Mysql Table Structure:
	-CREATE TABLE IF NOT EXISTS `notepad` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user` varchar(100) NOT NULL,
			`title` varchar(255) NOT NULL,
			`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`to` text NOT NULL COMMENT 'csv of people message was sent to',
			`body` text NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=198 ;

	-CREATE TABLE IF NOT EXISTS `users` (
			`user` varchar(32) NOT NULL,
			`pass` text NOT NULL,
			`note` text NOT NULL,
			PRIMARY KEY (`user`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;

	-the pass field contains a SHA1 hash defined as such:
	SHA1("{$_POST['ps123']}$salt") in function login()
	aka: it should be the hash of "passwordSALT"
	--Yes I know it's not truely secure, But it's good enough for now--

	You will have to create the usernames and password hashes manually.
	Leave everything else alone.


	ALSO:
	SEE "EMAIL MAPPINGS" and "FORM CHECKBOXES" in the code
	-This is to add people to email
	-should be a simple cut and paste job for both


