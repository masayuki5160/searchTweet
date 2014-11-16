-- DB作成
CREATE DATABASE INVERTED_INDEX DEFAULT CHARACTER SET utf8;

-- ドキュメント格納用のテーブル作成
CREATE TABLE documents( ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, TITLE TEXT NOT NULL, BODY TEXT NOT NULL );

-- 転置インデックス用のテーブル作成
CREATE TABLE tokens( ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT, TOKEN VARCHAR(255) NOT NULL, DOCS_COUNT INT NOT NULL, POSTINGS TEXT NOT NULL, INDEX token_index(TOKEN) );