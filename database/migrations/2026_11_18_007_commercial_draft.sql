-- DevIN comercial (RASCUNHO): não altera cobrança em produção.
-- Somente instalar em homologação; aplicar backup e conferência do esquema.
-- Preços NULL e is_published=0 garantem ausência de comercialização prematura.
CREATE TABLE IF NOT EXISTS devin_billing_plans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    audience ENUM('empresa','pessoa') NOT NULL,
    tier ENUM('nivel1','nivel2','nivel3') NOT NULL,
    title VARCHAR(100) NOT NULL,
    monthly_price_cents INT UNSIGNED NULL,
    max_active_jobs INT UNSIGNED NULL,
    max_applications_month INT UNSIGNED NULL,
    placement_fee_bps SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    hide_ads TINYINT(1) NOT NULL DEFAULT 1,
    auto_search TINYINT(1) NOT NULL DEFAULT 0,
    auto_apply TINYINT(1) NOT NULL DEFAULT 0,
    advanced_ai TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    stock INT UNSIGNED NULL,
    revision INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_devin_plan_tier (audience, tier),
    CHECK(placement_fee_bps<=500),
    CHECK(monthly_price_cents IS NULL OR monthly_price_cents>0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assinaturas dependem de confirmação de provedor, não apenas de formulário.
CREATE TABLE IF NOT EXISTS devin_billing_subscriptions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    owner_type ENUM('empresa','pessoa') NOT NULL,
    owner_id INT NOT NULL,
    provider VARCHAR(30) NULL,
    provider_subscription_id VARCHAR(160) NULL,
    status ENUM('pending','active','past_due','canceled','expired') NOT NULL DEFAULT 'pending',
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    next_renewal_at DATETIME NULL,
    cancel_requested_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_devin_subscription_owner (owner_type,owner_id,status),
    UNIQUE KEY uq_devin_payment_subscription (provider,provider_subscription_id),
    CONSTRAINT fk_devin_subscription_plan FOREIGN KEY(plan_id) REFERENCES devin_billing_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alterações de preço, limite, estoque e privilégio exigem ADM e trilha auditável.
CREATE TABLE IF NOT EXISTS devin_billing_plan_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    admin_id INT NOT NULL,
    action VARCHAR(35) NOT NULL,
    old_snapshot JSON NOT NULL,
    new_snapshot JSON NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_devin_plan_audit (plan_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventário de anúncios e campanhas com revisão de conteúdo.
CREATE TABLE IF NOT EXISTS devin_ad_campaigns (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    advertiser VARCHAR(180) NOT NULL,
    label VARCHAR(110) NOT NULL,
    destination_url VARCHAR(1024) NOT NULL,
    creative_text VARCHAR(500) NOT NULL,
    slot ENUM('feed','job_list','sidebar') NOT NULL,
    impression_budget INT UNSIGNED NOT NULL DEFAULT 0,
    impressions INT UNSIGNED NOT NULL DEFAULT 0,
    approved_by_admin INT NULL,
    status ENUM('draft','approved','paused','ended') NOT NULL DEFAULT 'draft',
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_devin_campaign_delivery (status,slot,starts_at,ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A taxa de intermediação refere-se a um serviço B2B após contratação bilateral.
-- Não descontar salários: cada evento precisa de valor contratado e validação externa.
CREATE TABLE IF NOT EXISTS devin_placement_fee_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    company_id INT NOT NULL,
    plan_id INT UNSIGNED NULL,
    verified_service_value_cents BIGINT UNSIGNED NOT NULL,
    applied_fee_bps SMALLINT UNSIGNED NOT NULL,
    fee_cents BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','verified','invoiced','paid','canceled') NOT NULL DEFAULT 'draft',
    reviewed_by_admin INT NULL,
    evidence_note VARCHAR(350) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_devin_placement_application (application_id),
    CHECK(applied_fee_bps<=500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preferência explícita: automação desativada até consentimento e ativação do plano.
CREATE TABLE IF NOT EXISTS devin_autoapply_preferences (
    owner_person_id INT NOT NULL PRIMARY KEY,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    max_monthly INT UNSIGNED NULL,
    criteria_json JSON NULL,
    consent_at DATETIME NULL,
    paused_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Níveis apenas demonstrativos; preços e publicação devem ser definidos por ADM
-- após análise financeira e integração de cobrança homologada.
INSERT IGNORE INTO devin_billing_plans
(audience,tier,title,max_active_jobs,max_applications_month,
 placement_fee_bps,hide_ads,auto_search,auto_apply,advanced_ai,is_published)
VALUES
('empresa','nivel1','Empresa Essencial',3,NULL,500,1,0,0,0,0),
('empresa','nivel2','Empresa Crescimento',10,NULL,300,1,0,0,0,0),
('empresa','nivel3','Empresa Escala',40,NULL,100,1,0,0,0,0),
('pessoa','nivel1','Pessoa Essencial',NULL,15,0,1,0,0,0,0),
('pessoa','nivel2','Pessoa Inteligente',NULL,40,0,1,1,0,0,0),
('pessoa','nivel3','Pessoa Pro',NULL,NULL,0,1,1,0,1,0);
