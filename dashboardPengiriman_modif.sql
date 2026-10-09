-- --------------------------------------------------------
-- Host:                         192.168.100.110
-- Server version:               8.0.11 - MySQL Community Server - GPL
-- Server OS:                    Linux
-- HeidiSQL Version:             12.0.0.6468
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for kemkes-ihs
CREATE DATABASE IF NOT EXISTS `kemkes-ihs` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `kemkes-ihs`;

-- Dumping structure for procedure kemkes-ihs.dashboardPengiriman_modif
DELIMITER //
CREATE PROCEDURE `dashboardPengiriman_modif`(
	IN `PTGL_AWAL` VARCHAR(20),
	IN `PTGL_AKHIR` VARCHAR(20)
)
BEGIN
	SELECT 'Organization' NAMA, SUM(IF(o.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(o.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.organization o
	UNION
	SELECT 'Location' NAMA, SUM(IF(o.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(o.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.location o
	UNION
	SELECT 'Patient' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.patient p
	UNION
	SELECT 'Practitioner' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.practitioner p
	UNION
	SELECT 'Encounter' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.encounter p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.refId
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Condition' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.`condition` p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Observation' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.observation p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Procedure' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.procedure p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Composition' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.composition p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Medication' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.medication p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Medication Request' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.medication_request p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Medication Dispance' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.medication_dispanse p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Service Request' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.service_request p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Specimen' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.specimen p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	UNION
	SELECT 'Diagnostic Report' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.diagnostic_report p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
	 UNION
	SELECT 'Allergy Intolerance' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.allergy_intolerance p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
		UNION
		SELECT 'Imagine Study' NAMA, SUM(IF(p.id IS NULL, 0, 1)) MEMILIKI_ID, SUM(IF(p.id IS NULL, 1, 0)) TDK_MEMILIKI_ID, COUNT(*) TOTAL
	  FROM `kemkes-ihs`.imaging_study p
	  LEFT JOIN
	  	pendaftaran.pendaftaran pen ON pen.NOMOR = p.nopen
	  WHERE pen.TANGGAL BETWEEN PTGL_AWAL AND PTGL_AKHIR
      ;
END//
DELIMITER ;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
