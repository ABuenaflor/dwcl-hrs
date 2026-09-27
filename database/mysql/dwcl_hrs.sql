-- DWCL HRDO — Applicant Filtering & Faculty Ranking System
-- MySQL 8 schema + reference data. Generated from the Laravel migrations/seeders.
-- Open in MySQL Workbench (File > Open SQL Script) and run, or use Database > Reverse Engineer after running it
-- to get the EER diagram. The preferred setup is still `php artisan migrate --seed`.
-- Default HRDO login: hrdo@dwcl.edu.ph / password  (change it immediately).

CREATE DATABASE IF NOT EXISTS `dwcl_hrs` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dwcl_hrs`;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_ranks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_points` decimal(7,2) NOT NULL DEFAULT '0.00',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_ranks_level_name_unique` (`level`,`name`),
  KEY `academic_ranks_level_index` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicant_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civil_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citizenship` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height_cm` decimal(5,1) DEFAULT NULL,
  `weight_kg` decimal(5,1) DEFAULT NULL,
  `blood_type` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagibig_no` text COLLATE utf8mb4_unicode_ci,
  `philhealth_no` text COLLATE utf8mb4_unicode_ci,
  `tin_no` text COLLATE utf8mb4_unicode_ci,
  `sss_no` text COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `residential_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_maiden_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applicant_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `applicant_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_documents_application_id_foreign` (`application_id`),
  CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned NOT NULL,
  `saw_criterion_id` bigint unsigned NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `rated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_scores_application_id_saw_criterion_id_unique` (`application_id`,`saw_criterion_id`),
  KEY `application_scores_saw_criterion_id_foreign` (`saw_criterion_id`),
  KEY `application_scores_rated_by_foreign` (`rated_by`),
  CONSTRAINT `application_scores_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_scores_rated_by_foreign` FOREIGN KEY (`rated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_scores_saw_criterion_id_foreign` FOREIGN KEY (`saw_criterion_id`) REFERENCES `saw_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `job_posting_id` bigint unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `years_experience` decimal(4,1) NOT NULL DEFAULT '0.0',
  `work_experience` text COLLATE utf8mb4_unicode_ci,
  `trainings` text COLLATE utf8mb4_unicode_ci,
  `trainings_count` smallint unsigned NOT NULL DEFAULT '0',
  `skills` text COLLATE utf8mb4_unicode_ci,
  `license` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_letter` text COLLATE utf8mb4_unicode_ci,
  `saw_score` decimal(8,6) DEFAULT NULL,
  `saw_rank` int unsigned DEFAULT NULL,
  `hr_notes` text COLLATE utf8mb4_unicode_ci,
  `interview_at` datetime DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_user_id_job_posting_id_unique` (`user_id`,`job_posting_id`),
  KEY `applications_job_posting_id_saw_rank_index` (`job_posting_id`,`saw_rank`),
  KEY `applications_status_index` (`status`),
  CONSTRAINT `applications_job_posting_id_foreign` FOREIGN KEY (`job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campuses_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campus_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_campus_id_name_unique` (`campus_id`,`name`),
  KEY `departments_level_index` (`level`),
  CONSTRAINT `departments_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `educations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inclusive_dates` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_graduated` smallint unsigned DEFAULT NULL,
  `honors` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `educations_user_id_level_index` (`user_id`,`level`),
  CONSTRAINT `educations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculty_ranking_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faculty_ranking_id` bigint unsigned NOT NULL,
  `rubric_item_id` bigint unsigned NOT NULL,
  `sr_points` decimal(7,2) DEFAULT NULL,
  `drc_points` decimal(7,2) DEFAULT NULL,
  `final_points` decimal(7,2) DEFAULT NULL,
  `evidence_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_ranking_scores_faculty_ranking_id_rubric_item_id_unique` (`faculty_ranking_id`,`rubric_item_id`),
  KEY `faculty_ranking_scores_rubric_item_id_foreign` (`rubric_item_id`),
  CONSTRAINT `faculty_ranking_scores_faculty_ranking_id_foreign` FOREIGN KEY (`faculty_ranking_id`) REFERENCES `faculty_rankings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `faculty_ranking_scores_rubric_item_id_foreign` FOREIGN KEY (`rubric_item_id`) REFERENCES `rubric_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculty_rankings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `rubric_id` bigint unsigned NOT NULL,
  `cycle` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sr_total` decimal(8,2) NOT NULL DEFAULT '0.00',
  `drc_total` decimal(8,2) DEFAULT NULL,
  `final_total` decimal(8,2) DEFAULT NULL,
  `current_rank_id` bigint unsigned DEFAULT NULL,
  `recommended_rank_id` bigint unsigned DEFAULT NULL,
  `certificate_no` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `certified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_rankings_user_id_cycle_unique` (`user_id`,`cycle`),
  UNIQUE KEY `faculty_rankings_certificate_no_unique` (`certificate_no`),
  KEY `faculty_rankings_rubric_id_foreign` (`rubric_id`),
  KEY `faculty_rankings_current_rank_id_foreign` (`current_rank_id`),
  KEY `faculty_rankings_recommended_rank_id_foreign` (`recommended_rank_id`),
  KEY `faculty_rankings_status_index` (`status`),
  CONSTRAINT `faculty_rankings_current_rank_id_foreign` FOREIGN KEY (`current_rank_id`) REFERENCES `academic_ranks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faculty_rankings_recommended_rank_id_foreign` FOREIGN KEY (`recommended_rank_id`) REFERENCES `academic_ranks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faculty_rankings_rubric_id_foreign` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `faculty_rankings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_postings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campus_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employment_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slots` smallint unsigned NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `qualifications` json NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `closes_at` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_postings_campus_id_foreign` (`campus_id`),
  KEY `job_postings_department_id_foreign` (`department_id`),
  KEY `job_postings_created_by_foreign` (`created_by`),
  KEY `job_postings_status_index` (`status`),
  CONSTRAINT `job_postings_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `job_postings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_postings_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ranking_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faculty_ranking_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `from_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ranking_reviews_faculty_ranking_id_foreign` (`faculty_ranking_id`),
  KEY `ranking_reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `ranking_reviews_faculty_ranking_id_foreign` FOREIGN KEY (`faculty_ranking_id`) REFERENCES `faculty_rankings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ranking_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubric_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rubric_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guide` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight_percent` decimal(5,2) DEFAULT NULL,
  `credit_points` decimal(7,2) DEFAULT NULL,
  `credit_in_field` decimal(7,2) DEFAULT NULL,
  `credit_related` decimal(7,2) DEFAULT NULL,
  `max_points` decimal(7,2) DEFAULT NULL,
  `is_scorable` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubric_items_parent_id_foreign` (`parent_id`),
  KEY `rubric_items_rubric_id_parent_id_sort_order_index` (`rubric_id`,`parent_id`,`sort_order`),
  CONSTRAINT `rubric_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `rubric_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rubric_items_rubric_id_foreign` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `total_points` decimal(7,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubrics_level_index` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saw_criteria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_posting_id` bigint unsigned NOT NULL,
  `key` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` decimal(5,4) NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'benefit',
  `source` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `scale_max` decimal(6,2) DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saw_criteria_job_posting_id_key_unique` (`job_posting_id`,`key`),
  CONSTRAINT `saw_criteria_job_posting_id_foreign` FOREIGN KEY (`job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campus_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `academic_rank_id` bigint unsigned DEFAULT NULL,
  `employment_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_employee_no_unique` (`employee_no`),
  KEY `users_campus_id_foreign` (`campus_id`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_academic_rank_id_foreign` (`academic_rank_id`),
  KEY `users_role_index` (`role`),
  KEY `users_status_index` (`status`),
  CONSTRAINT `users_academic_rank_id_foreign` FOREIGN KEY (`academic_rank_id`) REFERENCES `academic_ranks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- Reference data

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_00_000000_create_organization_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_09_27_054718_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_09_27_100000_create_hiring_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_09_27_100100_create_ranking_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `campuses` WRITE;
/*!40000 ALTER TABLE `campuses` DISABLE KEYS */;
INSERT INTO `campuses` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (1,'North Campus','Basic Education — Grade School, Junior and Senior High School','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `campuses` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (2,'South Campus','College Department and Graduate School','2026-09-26 22:18:27','2026-09-26 22:18:27');
/*!40000 ALTER TABLE `campuses` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (1,1,'Grade School','GS','basic_ed','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (2,1,'Junior High School','JHS','basic_ed','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (3,1,'Senior High School','SHS','basic_ed','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (4,2,'School of Engineering and Computer Studies','SOECS','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (5,2,'School of Education, Arts and Sciences','SEAS','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (6,2,'School of Business Management and Accountancy','SBMA','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (7,2,'School of Nursing','SON','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (8,2,'School of Hospitality Management','SHOM','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (9,2,'Graduate School of Business and Management','GSBM','tertiary','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (10,2,'Human Resources and Development Office','HRDO','non_academic','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (11,2,'Finance and Accounting Office','FAO','non_academic','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (12,2,'Registrar\'s Office','REG','non_academic','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (13,2,'Library','LIB','non_academic','2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `departments` (`id`, `campus_id`, `name`, `code`, `level`, `created_at`, `updated_at`) VALUES (14,2,'Property and Supply Office','PSO','non_academic','2026-09-26 22:18:27','2026-09-26 22:18:27');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `academic_ranks` WRITE;
/*!40000 ALTER TABLE `academic_ranks` DISABLE KEYS */;
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (1,'basic_ed','Teacher I',0.00,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (2,'basic_ed','Teacher II',150.00,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (3,'basic_ed','Teacher III',200.00,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (4,'basic_ed','Master Teacher I',250.00,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (5,'basic_ed','Master Teacher II',300.00,5,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (6,'basic_ed','Master Teacher III',350.00,6,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (7,'tertiary','Instructor I',0.00,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (8,'tertiary','Instructor II',40.00,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (9,'tertiary','Instructor III',55.00,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (10,'tertiary','Assistant Professor I',70.00,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (11,'tertiary','Assistant Professor II',80.00,5,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (12,'tertiary','Assistant Professor III',90.00,6,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (13,'tertiary','Associate Professor I',100.00,7,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (14,'tertiary','Associate Professor II',110.00,8,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (15,'tertiary','Associate Professor III',120.00,9,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (16,'tertiary','Professor I',130.00,10,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (17,'tertiary','Professor II',145.00,11,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `academic_ranks` (`id`, `level`, `name`, `min_points`, `sort_order`, `created_at`, `updated_at`) VALUES (18,'tertiary','Professor III',160.00,12,'2026-09-26 22:18:27','2026-09-26 22:18:27');
/*!40000 ALTER TABLE `academic_ranks` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `rubrics` WRITE;
/*!40000 ALTER TABLE `rubrics` DISABLE KEYS */;
INSERT INTO `rubrics` (`id`, `level`, `name`, `description`, `total_points`, `is_active`, `created_at`, `updated_at`) VALUES (1,'basic_ed','Basic Education Faculty Ranking Instrument','Based on the DWCL Faculty Manual (2017 revision).',400.00,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubrics` (`id`, `level`, `name`, `description`, `total_points`, `is_active`, `created_at`, `updated_at`) VALUES (2,'tertiary','College Faculty Ranking Instrument','Based on the DWCL Faculty Manual (2017 revision).',180.00,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
/*!40000 ALTER TABLE `rubrics` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `rubric_items` WRITE;
/*!40000 ALTER TABLE `rubric_items` DISABLE KEYS */;
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (1,1,NULL,'1','Educational Attainment',NULL,40.00,160.00,NULL,NULL,160.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (2,1,1,'1.1','Degrees Earned',NULL,20.00,NULL,NULL,NULL,80.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (3,1,2,'1.1.1','Baccalaureate',NULL,NULL,NULL,NULL,NULL,NULL,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (4,1,3,NULL,'BSE / BSEEd (or its equivalent)',NULL,NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (5,1,3,NULL,'BS / AB + LET Passer','5 points',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (6,1,2,'1.1.2','CS Exam (Professional)','2 points',NULL,NULL,NULL,NULL,2.00,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (7,1,1,'1.2','Professional Growth',NULL,20.00,NULL,NULL,NULL,80.00,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (8,1,7,'1.2.1','Advanced Training',NULL,NULL,NULL,NULL,NULL,NULL,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (9,1,8,NULL,'MA / MS units','1 pt / 6 units',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (10,1,8,NULL,'MA / MS + SO','40 points',NULL,NULL,NULL,NULL,40.00,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (11,1,7,'1.2.2','Seminars',NULL,NULL,NULL,NULL,NULL,25.00,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (12,1,11,NULL,'International','Attended 2 · Echoed 3 · Related field 1 — pts per 8 hrs',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (13,1,11,NULL,'National','Attended 1 · Echoed 2 · Related field ½ — pts per 8 hrs',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (14,1,11,NULL,'Regional','Attended ½ · Echoed 1 · Related field ¼ — pts per 8 hrs',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (15,1,11,NULL,'Local','Attended ¼ · Echoed ½ · Related field ⅛ — pts per 8 hrs',NULL,NULL,NULL,NULL,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (16,1,7,'1.2.3','As Resource Speaker',NULL,NULL,NULL,NULL,NULL,15.00,0,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (17,1,16,NULL,'Trainer / day','Nat\'l 10 · Reg\'l/Prov\'l 8 · District 6 · School 5',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (18,1,16,NULL,'Resource Speaker / topic','Nat\'l 8 · Reg\'l/Prov\'l 6 · District 5 · School 3',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (19,1,16,NULL,'Facilitator / day','Nat\'l 4 · Reg\'l/Prov\'l 3 · District 2 · School 1',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (20,1,7,'1.2.4','Completed Certificate of Proficiency',NULL,NULL,NULL,NULL,NULL,5.00,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (21,1,NULL,'2','Teaching Experience',NULL,25.00,100.00,NULL,NULL,100.00,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (22,1,21,'2.1','Status of Employment',NULL,NULL,NULL,NULL,NULL,100.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (23,1,22,'2.1.1','Full Time within DWCL','4 pts / yr of service',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (24,1,22,'2.1.2','Part Time (subject loading for 1 school year)',NULL,NULL,NULL,NULL,NULL,NULL,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (25,1,24,NULL,'1–2 subjects','1 pt / year',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (26,1,24,NULL,'3–4 subjects','2 pts / year',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (27,1,22,'2.1.3','Outside DWCL','1 pt / 3 yrs',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (28,1,NULL,'3','Faculty Performance Rating (Average Performance)',NULL,20.00,80.00,NULL,NULL,80.00,0,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (29,1,28,'3.1','Mode for the last 3 years',NULL,NULL,NULL,NULL,NULL,80.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (30,1,29,NULL,'4.0 – 4.5 Very Satisfactory','60 points',NULL,NULL,NULL,NULL,60.00,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (31,1,29,NULL,'3.6 – 3.9 Satisfactory','40 points',NULL,NULL,NULL,NULL,40.00,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (32,1,NULL,'4','Community Extension Services',NULL,10.00,40.00,NULL,NULL,40.00,0,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (33,1,32,'4.1','Professional Organizations, Societies, Civic, Social, Cultural, Religious Clubs, Groups, etc.',NULL,NULL,NULL,NULL,NULL,20.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (34,1,33,NULL,'International','5 pts / org',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (35,1,33,NULL,'National','4 pts / org',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (36,1,33,NULL,'Regional','3 pts / org',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (37,1,33,NULL,'Division / Provincial / School','1 pt / org',NULL,NULL,NULL,NULL,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (38,1,33,NULL,'Officership','Additional 1 pt',NULL,NULL,NULL,NULL,NULL,1,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (39,1,33,NULL,'Life membership','Additional 1 pt',NULL,NULL,NULL,NULL,NULL,1,5,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (40,1,32,'4.2','Services Rendered without Remuneration from DWCL',NULL,NULL,NULL,NULL,NULL,10.00,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (41,1,40,NULL,'Coach, trainer, facilitator','0.5 pts / event',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (42,1,40,NULL,'Area Chair — PAASCU','5 pts / visit',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (43,1,40,NULL,'Committee Member — PAASCU','2 pts / visit',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (44,1,40,NULL,'Chairman in other school activities','3 pts / activity',NULL,NULL,NULL,NULL,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (45,1,40,NULL,'Member in other school activities','2 pts / activity',NULL,NULL,NULL,NULL,NULL,1,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (46,1,40,NULL,'Membership in Academic Committees (Co- and Extra-Curricular, Ad Hoc, RTC, etc.)','2 pts / yr',NULL,NULL,NULL,NULL,NULL,1,5,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (47,1,32,'4.3','Awards, Citations, Plaques, Certificates',NULL,NULL,NULL,NULL,NULL,10.00,0,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (48,1,47,NULL,'International','5 pts / award',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (49,1,47,NULL,'National','4 pts / award',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (50,1,47,NULL,'Regional','2 pts / award',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (51,1,47,NULL,'Division / Provincial','1 pt / award',NULL,NULL,NULL,NULL,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (52,1,NULL,'5','Research Productivity (excluding theses, dissertations and commissioned research)',NULL,5.00,20.00,NULL,NULL,20.00,0,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (53,1,52,'5.1','Research Work / Participation in Research',NULL,NULL,NULL,NULL,NULL,20.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (54,1,53,NULL,'Main Researcher / Project Manager','5 pts',NULL,NULL,NULL,NULL,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (55,1,53,NULL,'Co-Researcher / Research or Statistics Consultant','3 pts',NULL,NULL,NULL,NULL,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (56,1,53,NULL,'Enumerator / Interviewer / Data Gatherer','1 pt',NULL,NULL,NULL,NULL,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (57,1,53,NULL,'Research Leader','2 pts',NULL,NULL,NULL,NULL,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (58,1,52,'5.2','Research output / modules, kits, manuals and other teaching materials submitted','5–20 pts as recommended by the Reviewing Committee; shared equally by group members',NULL,NULL,NULL,NULL,20.00,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (59,1,52,'5.3','Adoption of modules / kits','Additional 5 pts',NULL,NULL,NULL,NULL,5.00,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (60,2,NULL,'1','Educational Attainment','Maximum 60 points, cumulative',NULL,NULL,NULL,NULL,60.00,0,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (61,2,60,NULL,'Doctorate',NULL,NULL,NULL,50.00,45.00,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (62,2,60,NULL,'Extra Doctorate',NULL,NULL,NULL,15.00,5.00,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (63,2,60,NULL,'Master\'s Degree with Thesis',NULL,NULL,NULL,35.00,30.00,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (64,2,60,NULL,'Master\'s Degree without Thesis / Seminar Paper',NULL,NULL,NULL,30.00,25.00,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (65,2,60,NULL,'Extra Master\'s Degree with Thesis',NULL,NULL,NULL,5.00,4.00,NULL,1,4,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (66,2,60,NULL,'Extra Master\'s Degree without Thesis / Seminar Paper',NULL,NULL,NULL,5.00,3.00,NULL,1,5,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (67,2,60,NULL,'Extra Bachelor\'s Degree',NULL,NULL,NULL,3.00,2.00,NULL,1,6,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (68,2,60,NULL,'Board Exam',NULL,NULL,NULL,5.00,3.00,NULL,1,7,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (69,2,NULL,'2','Teaching and Professional Experience',NULL,NULL,NULL,NULL,NULL,25.00,0,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (70,2,69,NULL,'College teaching in DWCL','per year of service',NULL,NULL,2.00,1.50,NULL,1,0,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (71,2,69,NULL,'College teaching in other HEIs','per year',NULL,NULL,1.00,0.50,NULL,1,1,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (72,2,69,NULL,'Industry / professional practice','per year',NULL,NULL,1.00,0.50,NULL,1,2,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (73,2,69,NULL,'Administrative designation (Dean, Chair, Coordinator)','per year',NULL,NULL,1.00,1.00,NULL,1,3,'2026-09-26 22:18:27','2026-09-26 22:18:27');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (74,2,NULL,'3','Faculty Performance Evaluation (mode of the last 3 years)',NULL,NULL,NULL,NULL,NULL,20.00,0,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (75,2,74,NULL,'Outstanding',NULL,NULL,NULL,20.00,NULL,NULL,1,0,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (76,2,74,NULL,'Very Satisfactory',NULL,NULL,NULL,15.00,NULL,NULL,1,1,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (77,2,74,NULL,'Satisfactory',NULL,NULL,NULL,10.00,NULL,NULL,1,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (78,2,NULL,'4','Research and Publications',NULL,NULL,NULL,NULL,NULL,30.00,0,3,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (79,2,78,NULL,'Article in refereed international journal',NULL,NULL,NULL,10.00,8.00,NULL,1,0,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (80,2,78,NULL,'Article in refereed national journal',NULL,NULL,NULL,8.00,6.00,NULL,1,1,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (81,2,78,NULL,'Article in institutional journal',NULL,NULL,NULL,5.00,4.00,NULL,1,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (82,2,78,NULL,'Paper presented — international',NULL,NULL,NULL,6.00,5.00,NULL,1,3,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (83,2,78,NULL,'Paper presented — national',NULL,NULL,NULL,4.00,3.00,NULL,1,4,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (84,2,78,NULL,'Paper presented — regional / local',NULL,NULL,NULL,2.00,1.00,NULL,1,5,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (85,2,78,NULL,'Completed institutional research',NULL,NULL,NULL,3.00,2.00,NULL,1,6,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (86,2,78,NULL,'Published textbook / instructional material',NULL,NULL,NULL,5.00,4.00,NULL,1,7,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (87,2,NULL,'5','Professional Development',NULL,NULL,NULL,NULL,NULL,20.00,0,4,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (88,2,87,NULL,'Seminars / trainings attended — international','per 8 hrs',NULL,NULL,3.00,2.00,NULL,1,0,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (89,2,87,NULL,'Seminars / trainings attended — national','per 8 hrs',NULL,NULL,2.00,1.00,NULL,1,1,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (90,2,87,NULL,'Seminars / trainings attended — regional / local','per 8 hrs',NULL,NULL,1.00,0.50,NULL,1,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (91,2,87,NULL,'Resource speaker / trainer','per engagement',NULL,NULL,4.00,3.00,NULL,1,3,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (92,2,87,NULL,'Professional organization — officer','per org',NULL,NULL,2.00,1.00,NULL,1,4,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (93,2,87,NULL,'Professional organization — member','per org',NULL,NULL,1.00,0.50,NULL,1,5,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (94,2,NULL,'6','Community Extension Services',NULL,NULL,NULL,NULL,NULL,15.00,0,5,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (95,2,94,NULL,'Project leader / coordinator','per project',NULL,NULL,5.00,4.00,NULL,1,0,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (96,2,94,NULL,'Project member / facilitator','per project',NULL,NULL,3.00,2.00,NULL,1,1,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (97,2,94,NULL,'Participant','per activity',NULL,NULL,1.00,0.50,NULL,1,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (98,2,NULL,'7','Awards and Recognition',NULL,NULL,NULL,NULL,NULL,10.00,0,6,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (99,2,98,NULL,'International',NULL,NULL,NULL,5.00,4.00,NULL,1,0,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (100,2,98,NULL,'National',NULL,NULL,NULL,4.00,3.00,NULL,1,1,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (101,2,98,NULL,'Regional',NULL,NULL,NULL,3.00,2.00,NULL,1,2,'2026-09-26 22:18:28','2026-09-26 22:18:28');
INSERT INTO `rubric_items` (`id`, `rubric_id`, `parent_id`, `code`, `title`, `guide`, `weight_percent`, `credit_points`, `credit_in_field`, `credit_related`, `max_points`, `is_scorable`, `sort_order`, `created_at`, `updated_at`) VALUES (102,2,98,NULL,'Institutional',NULL,NULL,NULL,2.00,1.00,NULL,1,3,'2026-09-26 22:18:28','2026-09-26 22:18:28');
/*!40000 ALTER TABLE `rubric_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `email_verified_at`, `password`, `role`, `status`, `phone`, `employee_no`, `campus_id`, `department_id`, `academic_rank_id`, `employment_type`, `designation`, `date_hired`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES (1,'HRDO',NULL,'Administrator','hrdo@dwcl.edu.ph',NULL,'$2y$12$mgy53AbM1LbgHcGvFo1cHuKTIL5NsLVirvRaOMZShdysHsb8P6qr2','admin','active',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 22:18:28','2026-09-26 22:18:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

