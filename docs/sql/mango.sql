CREATE TABLE account_request (
	id integer not null primary key auto_increment,
	uid varchar(15) not null,
	cn varchar(255) not null,
	mail varchar(255) not null,
	comment text not null,
	timestamp datetime not null,
	authorizationkeys text not null,
	status char(1) default 'P' not null, -- M=mail_verification, V=awaiting_vouchers, R=rejected, A=approved, S=awaiting_setup
	is_new_account char(1) default 'Y' not null,
	is_mail_verified char(1) default 'N' not null,
	mail_token varchar(40) not null 
);

CREATE TABLE account_groups (
	id integer not null primary key auto_increment,
	request_id integer not null,
	cn varchar(15) not null,
	voucher_group varchar(50) null,
	verdict char(1) default 'P' not null,
	voucher varchar(15) null,
	denial_message varchar(255) null
);

