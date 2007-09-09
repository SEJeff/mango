CREATE TABLE account_request (
	id integer not null primary key auto_increment,
	uid varchar(11) not null,
	cn varchar(255) not null,
	email varchar(255) not null,
	comment text not null,
	timestamp datetime not null,
	authorizationkeys text not null,
	gnomemodule varchar(255) null, 
	translation varchar(255) null,
	svn_access varchar(11) default 'N' not null,
	ftp_access varchar(11) default 'N' not null,
	web_access varchar(11) default 'N' not null,
	bugzilla_access varchar(11) default 'N' not null,
	membctte varchar(11) default 'N' not null,
	art_access varchar(11) default 'N' not null,
	mail_alias varchar(11) default 'N' not null,
	mail_approved varchar(10) default 'pending' not null,
	maintainer_approved varchar(10) default 'pending' not null,
	denial_message varchar(255) null,
	verdict varchar(10) default 'pending' not null
);

CREATE TABLE account_token (
	id integer not null primary key auto_increment,
	request_id integer not null,
	token varchar(255) not null,
	status varchar(10) default 'pending' not null
);
