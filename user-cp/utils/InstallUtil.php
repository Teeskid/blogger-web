<?php
/**
 * Installation Utilities
 *
 * Helper functions for the site installation, also the database schema
 * 
 * @package Sevida
 * @subpackage Administration
 */
/**
 * Drops all the tables in database
 * @throws PDOException Provided that the query failed with mysql errors
 */
function dropAllTables() {
	global $_db;
	$myQuery = <<<'EOS'
	SET FOREIGN_KEY_CHECKS = 0;
	DROP TABLE IF EXISTS Config;
	DROP TABLE IF EXISTS Uzer;
	DROP TABLE IF EXISTS UzerMeta;
	DROP TABLE IF EXISTS Post;
	DROP TABLE IF EXISTS PostMeta;
	DROP TABLE IF EXISTS Reply;
	DROP TABLE IF EXISTS ReplyMeta;
	DROP TABLE IF EXISTS Term;
	DROP TABLE IF EXISTS TermLink;
	DROP TABLE IF EXISTS TermMeta;
	SET FOREIGN_KEY_CHECKS = 1;
EOS;
	$_db->exec( $myQuery );
}
/**
 * Populate the blog tables
 * @throws PDOException Provided that the query failed with mysql errors
 */
function createDbTables() {
	global $_db;
	$myQuery = <<<'EOS'
	SET time_zone = "+01:00";
	SET FOREIGN_KEY_CHECKS=0;

	CREATE TABLE Config (
	  metaKey varchar(20) PRIMARY KEY NOT NULL,
	  metaValue longtext DEFAULT NULL
	);

	CREATE TABLE Uzer (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  picture bigint(20) DEFAULT NULL,
	  userName varchar(20) DEFAULT NULL,
	  fullName tinytext DEFAULT NULL,
	  email varchar(64) DEFAULT NULL,
	  password varchar(128) DEFAULT NULL,
	  role varchar(20) DEFAULT NULL,
	  status varchar(20) DEFAULT NULL,
	  KEY status (status),
	  KEY picture (picture),
	  KEY role (role)
	) COLLATE=utf8mb4_bin;

	CREATE TABLE UzerMeta (
	  userId bigint(20) NOT NULL,
	  metaKey varchar(20) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY MemberMeta (userId,metaKey),
	  KEY userId (userId),
	  KEY metaKey (metaKey)
	) COLLATE=utf8mb4_bin;

	CREATE TABLE Post (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  thumbnail bigint(20) DEFAULT NULL,
	  category bigint(20) DEFAULT NULL,
	  author bigint(20) DEFAULT NULL,
	  title text NOT NULL,
	  permalink varchar(50) NOT NULL,
	  content longtext,
	  excerpt tinytext,
	  datePosted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  lastEdited timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  status varchar(20) DEFAULT NULL,
	  password varchar(100) DEFAULT NULL,
	  rowType varchar(20) DEFAULT NULL,
	  mimeType varchar(30) DEFAULT NULL,
	  viewCount bigint(20) NOT NULL DEFAULT '0',
	  UNIQUE KEY permalink (permalink),
	  KEY thumbnail (thumbnail),
	  KEY category (category),
	  KEY author (author),
	  KEY datePosted (datePosted),
	  KEY lastEdited (lastEdited),
	  KEY viewCount (viewCount),
	  KEY status (status),
	  KEY mimeType (mimeType),
	  KEY rowType (rowType)
	) COLLATE=utf8mb4_bin;

	CREATE TABLE PostMeta (
	  postId bigint(20) NOT NULL,
	  metaKey varchar(20) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY PostMeta (postId,metaKey),
	  KEY postId (postId),
	  KEY metaKey (metaKey)
	) COLLATE=utf8mb4_bin;
	CREATE TABLE Term (
	  id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	  inTerm bigint(20) DEFAULT NULL,
	  title tinytext NOT NULL,
	  permalink varchar(50) UNIQUE KEY NOT NULL,
	  about tinytext DEFAULT NULL,
	  childCount bigint(20) NOT NULL DEFAULT '0',
	  rowType varchar(20) NOT NULL,
	  KEY rowType (rowType),
	  KEY inTerm (inTerm)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
	CREATE TABLE TermLink (
	  postId bigint(20) NOT NULL,
	  termId bigint(20) NOT NULL,
	  KEY termId (termId),
	  KEY postId (postId)
	) COLLATE=utf8mb4_bin;
	CREATE TABLE TermMeta (
	  termId bigint(20) NOT NULL,
	  metaKey varchar(100) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY TermMeta (termId, metaKey),
	  KEY termId (termId),
	  KEY metaKey (metaKey)
	) COLLATE=utf8mb4_bin;
	CREATE TABLE Reply (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  postId bigint(20) NOT NULL,
	  inReply bigint(20) NOT NULL,
	  author bigint(20) DEFAULT NULL,
	  fullName tinytext DEFAULT NULL,
	  email varchar(100) DEFAULT NULL,
	  website varchar(200) DEFAULT NULL,
	  ipaddress varchar(20) NOT NULL,
	  content text NOT NULL,
	  replied timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  status varchar(20) DEFAULT NULL,
	  PRIMARY KEY (id),
	  KEY inReply (inReply),
	  KEY postId (postId),
	  KEY author (author),
	  KEY replied (replied),
	  KEY email (email),
	  KEY status (status)
	) COLLATE=utf8mb4_bin;
	CREATE TABLE ReplyMeta (
	  replyId bigint(20) NOT NULL DEFAULT '0',
	  metaKey varchar(100) NOT NULL,
	  metaValue longtext,
	  UNIQUE KEY ReplyMeta (replyId,metaKey),
	  KEY replyId (replyId),
	  KEY metaKey (metaKey)
	) COLLATE=utf8mb4_bin;

	ALTER TABLE Uzer
	  ADD CONSTRAINT FK_Uzer_Post FOREIGN KEY (picture) REFERENCES Post (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE UzerMeta
	  ADD CONSTRAINT FK_UzerMeta_Uzer FOREIGN KEY (userId) REFERENCES Uzer (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Post
	  ADD CONSTRAINT FK_Post_Uzer FOREIGN KEY (author) REFERENCES Uzer (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Post_Term FOREIGN KEY (category) REFERENCES Term (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Post_Post FOREIGN KEY (thumbnail) REFERENCES Post (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE PostMeta
	  ADD CONSTRAINT FK_PostMeta_Post FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Reply
	  ADD CONSTRAINT FK_Reply_Uzer FOREIGN KEY (author) REFERENCES Uzer (id) ON DELETE SET NULL ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Reply_Reply FOREIGN KEY (inReply) REFERENCES Reply (id) ON DELETE CASCADE ON UPDATE CASCADE,
	  ADD CONSTRAINT FK_Reply_PostId FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE ReplyMeta
	  ADD CONSTRAINT FK_ReplyMeta_Reply FOREIGN KEY (replyId) REFERENCES Reply (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE Term
	  ADD CONSTRAINT FK_Term_Term FOREIGN KEY (inTerm) REFERENCES Term (id) ON DELETE SET NULL ON UPDATE CASCADE;

	ALTER TABLE TermLink
	  ADD CONSTRAINT TermLink_Term FOREIGN KEY (termId) REFERENCES Term (id) ON DELETE CASCADE ON UPDATE CASCADE,
	  ADD CONSTRAINT TermLink_Post FOREIGN KEY (postId) REFERENCES Post (id) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE TermMeta
	  ADD CONSTRAINT FK_TermMeta_Term FOREIGN KEY (termId) REFERENCES Term (id) ON DELETE CASCADE ON UPDATE CASCADE;

	SET FOREIGN_KEY_CHECKS=1;
EOS;
	$_db->exec( $myQuery );
	unset( $myQuery );
}
