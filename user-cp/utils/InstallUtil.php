<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
function dropTables() {
	global $db;
	$DROPPER_QUERY = <<<'EOS'
	SET FOREIGN_KEY_CHECKS = 0;

	DROP TABLE IF EXISTS Config;
	DROP TABLE IF EXISTS Person;
	DROP TABLE IF EXISTS PersonMeta;
	DROP TABLE IF EXISTS Post;
	DROP TABLE IF EXISTS PostMeta;
	DROP TABLE IF EXISTS Reply;
	DROP TABLE IF EXISTS ReplyMeta;
	DROP TABLE IF EXISTS Term;
	DROP TABLE IF EXISTS TermLink;
	DROP TABLE IF EXISTS TermMeta;

	SET FOREIGN_KEY_CHECKS = 1;
EOS;
	$db->exec( $DROPPER_QUERY );
	unset( $DROPPER_QUERY );
}
function createTables() {
	global $db;
	$INSTALL_QUERY = <<<'EOS'
	SET time_zone = "+01:00";
	SET FOREIGN_KEY_CHECKS=0;

	CREATE TABLE Config (
	  metaKey varchar(20) PRIMARY KEY NOT NULL,
	  metaValue longtext DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

	CREATE TABLE Person (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  picture bigint(20) DEFAULT NULL,
	  userName varchar(50) UNIQUE KEY NOT NULL,
	  fullName tinytext DEFAULT NULL,
	  email varchar(50) DEFAULT NULL,
	  password varchar(50) DEFAULT NULL,
	  role varchar(20) DEFAULT NULL,
	  status varchar(20) DEFAULT NULL,
	  KEY status (status),
	  KEY picture (picture),
	  KEY role (role)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE PersonMeta (
	  personId bigint(20) NOT NULL,
	  metaKey varchar(20) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY MemberMeta (personId,metaKey),
	  KEY personId (personId),
	  KEY metaKey (metaKey)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE Post (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  thumbnail bigint(20) DEFAULT NULL,
	  category bigint(20) DEFAULT NULL,
	  author bigint(20) DEFAULT NULL,
	  title text NOT NULL,
	  permalink varchar(50) NOT NULL,
	  content longtext,
	  excerpt tinytext,
	  posted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  status varchar(20) DEFAULT NULL,
	  password varchar(100) DEFAULT NULL,
	  subject varchar(20) DEFAULT NULL,
	  mimeType varchar(30) DEFAULT NULL,
	  views bigint(20) NOT NULL DEFAULT '0',
	  UNIQUE KEY permalink (permalink),
	  KEY thumbnail (thumbnail),
	  KEY category (category),
	  KEY author (author),
	  KEY posted (posted),
	  KEY modified (modified),
	  KEY views (views),
	  KEY status (status),
	  KEY mimeType (mimeType),
	  KEY subject (subject)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE PostMeta (
	  postId bigint(20) NOT NULL,
	  metaKey varchar(20) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY PostMeta (postId,metaKey),
	  KEY postId (postId),
	  KEY metaKey (metaKey)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE Term (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  master bigint(20) DEFAULT NULL,
	  permalink varchar(50) UNIQUE KEY NOT NULL,
	  title tinytext NOT NULL,
	  about tinytext DEFAULT NULL,
	  objects bigint(20) NOT NULL DEFAULT '0',
	  subject varchar(20) NOT NULL,
	  KEY subject (subject),
	  KEY master (master)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE TermLink (
	  postId bigint(20) NOT NULL,
	  termId bigint(20) NOT NULL,
	  KEY termId (termId),
	  KEY postId (postId)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE TermMeta (
	  termId bigint(20) NOT NULL,
	  metaKey varchar(100) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY TermMeta (termId, metaKey),
	  KEY termId (termId),
	  KEY metaKey (metaKey)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE Reply (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  postId bigint(20) NOT NULL,
	  master bigint(20) NOT NULL,
	  author bigint(20) DEFAULT NULL,
	  fullName tinytext DEFAULT NULL,
	  email varchar(100) DEFAULT NULL,
	  website varchar(200) DEFAULT NULL,
	  ipaddress varchar(20) NOT NULL,
	  content text NOT NULL,
	  replied timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  status varchar(20) DEFAULT NULL,
	  PRIMARY KEY (id),
	  KEY master (master),
	  KEY postId (postId),
	  KEY author (author),
	  KEY replied (replied),
	  KEY email (email),
	  KEY status (status)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	CREATE TABLE ReplyMeta (
	  replyId bigint(20) NOT NULL DEFAULT '0',
	  metaKey varchar(100) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY ReplyMeta (replyId,metaKey),
	  KEY replyId (replyId),
	  KEY metaKey (metaKey)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

	ALTER TABLE Person
	  ADD CONSTRAINT FK_Person_Post FOREIGN KEY (picture) REFERENCES Post (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE PersonMeta
	  ADD CONSTRAINT FK_PersonMeta_Person FOREIGN KEY (personId) REFERENCES Person (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Post
	  ADD CONSTRAINT FK_Post_Person FOREIGN KEY (author) REFERENCES Person (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Post_Term FOREIGN KEY (category) REFERENCES Term (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Post_Post FOREIGN KEY (thumbnail) REFERENCES Post (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE PostMeta
	  ADD CONSTRAINT FK_PostMeta_Post FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Reply
	  ADD CONSTRAINT FK_Reply_Person FOREIGN KEY (author) REFERENCES Person (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Reply_Reply FOREIGN KEY (master) REFERENCES Reply (id) ON DELETE CASCADE ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Reply_PostId FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE ReplyMeta
	  ADD CONSTRAINT FK_ReplyMeta_Reply FOREIGN KEY (replyId) REFERENCES Reply (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Term
	  ADD CONSTRAINT FK_Term_Term FOREIGN KEY (master) REFERENCES Term (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE TermLink
	  ADD CONSTRAINT TermLink_Term FOREIGN KEY (termId) REFERENCES Term (id) ON DELETE CASCADE ON UPDATE CASCADE,
	  ADD CONSTRAINT TermLink_Post FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE TermMeta
	  ADD CONSTRAINT FK_TermMeta_Term FOREIGN KEY (termId) REFERENCES Term (id) ON DELETE CASCADE ON UPDATE CASCADE;

	SET FOREIGN_KEY_CHECKS=1;
EOS;
	$db->exec( $INSTALL_QUERY );
	unset( $INSTALL_QUERY );
}