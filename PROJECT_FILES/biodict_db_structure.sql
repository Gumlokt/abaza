--
-- Скрипт сгенерирован Devart dbForge Studio for MySQL, Версия 6.3.358.0
-- Домашняя страница продукта: http://www.devart.com/ru/dbforge/mysql/studio
-- Дата скрипта: 01.07.2015 10:20:03
-- Версия сервера: 5.5.5-10.0.17-MariaDB-log
-- Версия клиента: 4.1
--


--
-- Описание для базы данных biodict
--
DROP DATABASE IF EXISTS biodict;
CREATE DATABASE IF NOT EXISTS biodict
CHARACTER SET utf8
COLLATE utf8_general_ci;

-- 
-- Установка базы данных по умолчанию
--
USE biodict; SET NAMES utf8;

--
-- Описание для таблицы biodict.A_Word
--
CREATE TABLE IF NOT EXISTS biodict.A_Word (
  id int(11) NOT NULL AUTO_INCREMENT,
  a_word text NOT NULL,
  a_word_stressed text NOT NULL,
  native tinyint(4) NOT NULL DEFAULT 1,
  dialect text NOT NULL,
  latin text NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'Абазинские и латинские наименования расстений';

--
-- Описание для таблицы biodict.Full_Description
--
CREATE TABLE IF NOT EXISTS biodict.Full_Description (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_A_Word int(11) NOT NULL,
  full_description text NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_Full_Description_A_Word_id FOREIGN KEY (id_A_Word)
  REFERENCES biodict.A_Word (id) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'Полное описание расстения';

--
-- Описание для таблицы biodict.Link
--
CREATE TABLE IF NOT EXISTS biodict.Link (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_A_Word int(11) NOT NULL,
  link text NOT NULL,
  comment text NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_Link_A_Word_id FOREIGN KEY (id_A_Word)
  REFERENCES biodict.A_Word (id) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'Ссылки на абазинские слова';

--
-- Описание для таблицы biodict.R_Word
--
CREATE TABLE IF NOT EXISTS biodict.R_Word (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_A_Word int(11) NOT NULL,
  r_translation text NOT NULL,
  r_addition text NOT NULL,
  r_comment text NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_R_Word_A_Word_id FOREIGN KEY (id_A_Word)
  REFERENCES biodict.A_Word (id) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'Русские переводы';