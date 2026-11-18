<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Endpoint administrativo comercial: rascunhos e assinantes.
 * Nenhum caminho de compra/ativação está exposto, portanto não cobra ninguém.
 * O JARVIS pode preparar propostas, mas executar requer sessão ADM, CSRF,
 * permissão de gestão e revisão da versão do plano.
 */
require_once __DIR__ . '/../../middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Security/AdminRolePolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/CommercialPlanCatalog.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
$user=requireWebAuth('adm'); // Explicação DevIN: Atribui um valor à variável PHP $user.
$adminId=(int)$user['id']; // Explicação DevIN: Atribui um valor à variável PHP $adminId.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $db=getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if(($_SERVER['REQUEST_METHOD'] ?? '')==='GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $action=(string)($_GET['action']??'plans'); // Explicação DevIN: Atribui um valor à variável PHP $action.
            if(!in_array($action,['plans','subscribers'],true)) throw new InvalidArgumentException('Consulta inválida.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $rows=$action==='plans'?CommercialPlanCatalog::all($db):CommercialPlanCatalog::subscribers($db); // Explicação DevIN: Atribui um valor à variável PHP $rows.
            echo json_encode(['success'=>true,'rows'=>$rows],JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            http_response_code(405); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            echo json_encode(['success'=>false,'message'=>'Método não permitido.']); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        AdminRolePolicy::requireAction($db,$adminId,'update_settings'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $changes=json_decode((string)($_POST['changes']??''),true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if(!is_array($changes)||count($changes)>9) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Envie um conjunto pequeno de alterações válidas.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id=filter_var($_POST['plan_id']??null,FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $rev=filter_var($_POST['revision']??null,FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $rev.
        if(!$id || !$rev) throw new InvalidArgumentException('Identificador ou revisão inválida.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $plan=CommercialPlanCatalog::updateDraft($db,$adminId,$id,$rev,$changes); // Explicação DevIN: Atribui um valor à variável PHP $plan.
        echo json_encode(['success'=>true,'plan'=>$plan],JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (InvalidArgumentException|DomainException $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    http_response_code(422); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode(['success'=>false,'message'=>$error->getMessage()],JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
} catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Commercial plan admin: '.get_class($error)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode(['success'=>false,'message'=>'Não foi possível consultar ou atualizar planos.'],JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
