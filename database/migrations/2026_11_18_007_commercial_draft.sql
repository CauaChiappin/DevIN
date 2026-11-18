-- DevIN comercial (RASCUNHO): não altera cobrança em produção.
-- Somente instalar em homologação; aplicar backup e conferência do esquema.
-- Preços NULL e is_published=0 garantem ausência de comercialização prematura.
CREATE TABLE IF NOT EXISTS devin_billing_plans ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    audience ENUM('empresa','pessoa') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    tier ENUM('nivel1','nivel2','nivel3') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    title VARCHAR(100) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    monthly_price_cents INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    max_active_jobs INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    max_applications_month INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    placement_fee_bps SMALLINT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    hide_ads TINYINT(1) NOT NULL DEFAULT 1, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    auto_search TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    auto_apply TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    advanced_ai TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    is_published TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    stock INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    revision INT UNSIGNED NOT NULL DEFAULT 1, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    UNIQUE KEY uq_devin_plan_tier (audience, tier), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    CHECK(placement_fee_bps<=500), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    CHECK(monthly_price_cents IS NULL OR monthly_price_cents>0) -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Assinaturas dependem de confirmação de provedor, não apenas de formulário.
CREATE TABLE IF NOT EXISTS devin_billing_subscriptions ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    plan_id INT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    owner_type ENUM('empresa','pessoa') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    owner_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    provider VARCHAR(30) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    provider_subscription_id VARCHAR(160) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('pending','active','past_due','canceled','expired') NOT NULL DEFAULT 'pending', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    starts_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    ends_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    next_renewal_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    cancel_requested_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_devin_subscription_owner (owner_type,owner_id,status), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    UNIQUE KEY uq_devin_payment_subscription (provider,provider_subscription_id), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    CONSTRAINT fk_devin_subscription_plan FOREIGN KEY(plan_id) REFERENCES devin_billing_plans(id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Alterações de preço, limite, estoque e privilégio exigem ADM e trilha auditável.
CREATE TABLE IF NOT EXISTS devin_billing_plan_audit ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    plan_id INT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    action VARCHAR(35) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    old_snapshot JSON NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    new_snapshot JSON NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_devin_plan_audit (plan_id,created_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Inventário de anúncios e campanhas com revisão de conteúdo.
CREATE TABLE IF NOT EXISTS devin_ad_campaigns ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    advertiser VARCHAR(180) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    label VARCHAR(110) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    destination_url VARCHAR(1024) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    creative_text VARCHAR(500) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    slot ENUM('feed','job_list','sidebar') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    impression_budget INT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    impressions INT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    approved_by_admin INT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('draft','approved','paused','ended') NOT NULL DEFAULT 'draft', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    starts_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    ends_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_devin_campaign_delivery (status,slot,starts_at,ends_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- A taxa de intermediação refere-se a um serviço B2B após contratação bilateral.
-- Não descontar salários: cada evento precisa de valor contratado e validação externa.
CREATE TABLE IF NOT EXISTS devin_placement_fee_events ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    application_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    company_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    plan_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    verified_service_value_cents BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    applied_fee_bps SMALLINT UNSIGNED NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    fee_cents BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('draft','verified','invoiced','paid','canceled') NOT NULL DEFAULT 'draft', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    reviewed_by_admin INT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    evidence_note VARCHAR(350) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    UNIQUE KEY uq_devin_placement_application (application_id), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    CHECK(applied_fee_bps<=500) -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Preferência explícita: automação desativada até consentimento e ativação do plano.
CREATE TABLE IF NOT EXISTS devin_autoapply_preferences ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    owner_person_id INT NOT NULL PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    enabled TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    max_monthly INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    criteria_json JSON NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    consent_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    paused_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Níveis apenas demonstrativos; preços e publicação devem ser definidos por ADM
-- após análise financeira e integração de cobrança homologada.
INSERT IGNORE INTO devin_billing_plans -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
(audience,tier,title,max_active_jobs,max_applications_month, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 placement_fee_bps,hide_ads,auto_search,auto_apply,advanced_ai,is_published) -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
VALUES -- Explicação DevIN: Completa cláusula de consulta ou manipulação SQL.
('empresa','nivel1','Empresa Essencial',3,NULL,500,1,0,0,0,0), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
('empresa','nivel2','Empresa Crescimento',10,NULL,300,1,0,0,0,0), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
('empresa','nivel3','Empresa Escala',40,NULL,100,1,0,0,0,0), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
('pessoa','nivel1','Pessoa Essencial',NULL,15,0,1,0,0,0,0), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
('pessoa','nivel2','Pessoa Inteligente',NULL,40,0,1,1,0,0,0), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
('pessoa','nivel3','Pessoa Pro',NULL,NULL,0,1,1,0,1,0); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
