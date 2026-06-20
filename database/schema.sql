-- ============================================================
-- CSU-Piat AOPCR/IPCR Generation System — Database Schema
-- Run this AFTER creating the database: csu_piat_aopcr_ipcr
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Departments / Offices
CREATE TABLE IF NOT EXISTS departments (
  id         VARCHAR(10)  NOT NULL,
  name       VARCHAR(200) NOT NULL,
  type       ENUM('admin','academic') NOT NULL,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id                 INT          NOT NULL AUTO_INCREMENT,
  username           VARCHAR(50)  NOT NULL,
  password           VARCHAR(255) NOT NULL,
  role               ENUM('superadmin','admin','user') NOT NULL DEFAULT 'user',
  name               VARCHAR(100) NOT NULL,
  position           VARCHAR(150) DEFAULT NULL,
  department_id      VARCHAR(10)  DEFAULT NULL,
  email              VARCHAR(100) DEFAULT NULL,
  gender             ENUM('Male','Female','Other') DEFAULT NULL,
  status             ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending',
  avatar             VARCHAR(10)  DEFAULT NULL,
  security_question  VARCHAR(200) DEFAULT NULL,
  security_answer    VARCHAR(255) DEFAULT NULL,
  last_login         DATETIME     DEFAULT NULL,
  created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  KEY fk_users_dept (department_id),
  CONSTRAINT fk_users_dept FOREIGN KEY (department_id) REFERENCES departments (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Academic Timelines
CREATE TABLE IF NOT EXISTS timelines (
  id                  INT         NOT NULL AUTO_INCREMENT,
  academic_year       VARCHAR(20) NOT NULL,
  semester            VARCHAR(30) NOT NULL,
  start_date          DATE        NOT NULL,
  end_date            DATE        NOT NULL,
  submission_deadline DATE        NOT NULL,
  status              ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_by          INT         DEFAULT NULL,
  created_at          TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_timeline_creator (created_by),
  CONSTRAINT fk_timeline_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KPI / Performance Indicators
CREATE TABLE IF NOT EXISTS kpi_items (
  id                INT         NOT NULL AUTO_INCREMENT,
  category          ENUM('core','strategic','support') NOT NULL,
  mfo               VARCHAR(100) DEFAULT NULL,
  success_indicator TEXT        NOT NULL,
  target            VARCHAR(200) DEFAULT NULL,
  measure           VARCHAR(200) DEFAULT NULL,
  department_id     VARCHAR(10)  DEFAULT NULL,
  is_active         TINYINT(1)  NOT NULL DEFAULT 1,
  created_by        INT         DEFAULT NULL,
  created_at        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_kpi_dept (department_id),
  KEY fk_kpi_creator (created_by),
  CONSTRAINT fk_kpi_dept    FOREIGN KEY (department_id) REFERENCES departments (id) ON UPDATE CASCADE,
  CONSTRAINT fk_kpi_creator FOREIGN KEY (created_by)   REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IPCR Forms
CREATE TABLE IF NOT EXISTS ipcr_forms (
  id              INT          NOT NULL AUTO_INCREMENT,
  user_id         INT          NOT NULL,
  timeline_id     INT          NOT NULL,
  covered_period  VARCHAR(100) DEFAULT NULL,
  date_submitted  DATE         DEFAULT NULL,
  status          ENUM('draft','pending','reviewed','approved','disapproved') NOT NULL DEFAULT 'draft',
  overall_rating  DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  remarks         TEXT         DEFAULT NULL,
  reviewed_by     INT          DEFAULT NULL,
  reviewed_at     DATETIME     DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_ipcr_user     (user_id),
  KEY fk_ipcr_timeline (timeline_id),
  KEY fk_ipcr_reviewer (reviewed_by),
  CONSTRAINT fk_ipcr_user     FOREIGN KEY (user_id)     REFERENCES users (id),
  CONSTRAINT fk_ipcr_timeline FOREIGN KEY (timeline_id) REFERENCES timelines (id),
  CONSTRAINT fk_ipcr_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IPCR Line Items
CREATE TABLE IF NOT EXISTS ipcr_items (
  id              INT          NOT NULL AUTO_INCREMENT,
  ipcr_form_id    INT          NOT NULL,
  kpi_id          INT          DEFAULT NULL,
  function_type   ENUM('core','strategic','support') NOT NULL,
  success_indicator TEXT       DEFAULT NULL,
  accomplishment  TEXT         DEFAULT NULL,
  rating          TINYINT      DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
  remarks         VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_ipcr_item_form (ipcr_form_id),
  KEY fk_ipcr_item_kpi  (kpi_id),
  CONSTRAINT fk_ipcr_item_form FOREIGN KEY (ipcr_form_id) REFERENCES ipcr_forms (id) ON DELETE CASCADE,
  CONSTRAINT fk_ipcr_item_kpi  FOREIGN KEY (kpi_id)       REFERENCES kpi_items (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OPCR Forms
CREATE TABLE IF NOT EXISTS opcr_forms (
  id              INT          NOT NULL AUTO_INCREMENT,
  admin_id        INT          NOT NULL,
  department_id   VARCHAR(10)  NOT NULL,
  timeline_id     INT          NOT NULL,
  covered_period  VARCHAR(100) DEFAULT NULL,
  date_submitted  DATE         DEFAULT NULL,
  status          ENUM('draft','pending','reviewed','approved','disapproved') NOT NULL DEFAULT 'draft',
  overall_rating  DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  remarks         TEXT         DEFAULT NULL,
  reviewed_by     INT          DEFAULT NULL,
  reviewed_at     DATETIME     DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_opcr_admin    (admin_id),
  KEY fk_opcr_dept     (department_id),
  KEY fk_opcr_timeline (timeline_id),
  KEY fk_opcr_reviewer (reviewed_by),
  CONSTRAINT fk_opcr_admin    FOREIGN KEY (admin_id)     REFERENCES users (id),
  CONSTRAINT fk_opcr_dept     FOREIGN KEY (department_id) REFERENCES departments (id),
  CONSTRAINT fk_opcr_timeline FOREIGN KEY (timeline_id)  REFERENCES timelines (id),
  CONSTRAINT fk_opcr_reviewer FOREIGN KEY (reviewed_by)  REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OPCR Line Items
CREATE TABLE IF NOT EXISTS opcr_items (
  id                INT          NOT NULL AUTO_INCREMENT,
  opcr_form_id      INT          NOT NULL,
  function_type     ENUM('core','strategic','support') NOT NULL,
  mfo               VARCHAR(100) DEFAULT NULL,
  success_indicator TEXT         DEFAULT NULL,
  target            VARCHAR(200) DEFAULT NULL,
  actual            TEXT         DEFAULT NULL,
  budget            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  rating            TINYINT      DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
  PRIMARY KEY (id),
  KEY fk_opcr_item_form (opcr_form_id),
  CONSTRAINT fk_opcr_item_form FOREIGN KEY (opcr_form_id) REFERENCES opcr_forms (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evidence / Supporting Documents
CREATE TABLE IF NOT EXISTS evidence_files (
  id            INT           NOT NULL AUTO_INCREMENT,
  ipcr_form_id  INT           NOT NULL,
  user_id       INT           NOT NULL,
  original_name VARCHAR(255)  NOT NULL,
  stored_name   VARCHAR(255)  NOT NULL,
  file_path     VARCHAR(500)  NOT NULL,
  file_size     INT           NOT NULL DEFAULT 0,
  mime_type     VARCHAR(100)  DEFAULT NULL,
  category      ENUM('core','strategic','support','other') NOT NULL DEFAULT 'other',
  description   TEXT          DEFAULT NULL,
  uploaded_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_evidence_ipcr (ipcr_form_id),
  KEY fk_evidence_user (user_id),
  CONSTRAINT fk_evidence_ipcr FOREIGN KEY (ipcr_form_id) REFERENCES ipcr_forms (id) ON DELETE CASCADE,
  CONSTRAINT fk_evidence_user FOREIGN KEY (user_id)      REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id         INT       NOT NULL AUTO_INCREMENT,
  user_id    INT       NOT NULL,
  type       ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  message    TEXT      NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_notif_user (user_id),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
  id         INT          NOT NULL AUTO_INCREMENT,
  user_id    INT          NOT NULL,
  activity   TEXT         NOT NULL,
  ip_address VARCHAR(45)  DEFAULT NULL,
  user_agent VARCHAR(300) DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_log_user (user_id),
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts (server-side rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
  id         INT         NOT NULL AUTO_INCREMENT,
  username   VARCHAR(50) NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_attempts_username (username),
  KEY idx_login_attempts_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
