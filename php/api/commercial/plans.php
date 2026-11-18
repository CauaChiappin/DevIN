<?php
declare(strict_types=1);

/**
 * Endpoint administrativo comercial: rascunhos e assinantes.
 * Nenhum caminho de compra/ativação está exposto, portanto não cobra ninguém.
 * O JARVIS pode preparar propostas, mas executar requer sessão ADM, CSRF,
 * permissão de gestão e revisão da versão do plano.
 */
require_once __DIR__ . '/../../middlewares/auth.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/Security/AdminRolePolicy.php';
require_once __DIR__ . '/../../controllers/CommercialPlanCatalog.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$user=requireWebAuth('adm');
$adminId=(int)$user['id'];
try {
    $db=getDatabaseConnection();
    try {
        if(($_SERVER['REQUEST_METHOD'] ?? '')==='GET') {
            $action=(string)($_GET['action']??'plans');
            if(!in_array($action,['plans','subscribers'],true)) throw new InvalidArgumentException('Consulta inválida.');
            $rows=$action==='plans'?CommercialPlanCatalog::all($db):CommercialPlanCatalog::subscribers($db);
            echo json_encode(['success'=>true,'rows'=>$rows],JSON_UNESCAPED_UNICODE);
            exit;
        }
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST') {
            http_response_code(405);
            echo json_encode(['success'=>false,'message'=>'Método não permitido.']);
            exit;
        }
        requireValidCsrf();
        AdminRolePolicy::requireAction($db,$adminId,'update_settings');
        $changes=json_decode((string)($_POST['changes']??''),true);
        if(!is_array($changes)||count($changes)>9) {
            throw new InvalidArgumentException('Envie um conjunto pequeno de alterações válidas.');
        }
        $id=filter_var($_POST['plan_id']??null,FILTER_VALIDATE_INT);
        $rev=filter_var($_POST['revision']??null,FILTER_VALIDATE_INT);
        if(!$id || !$rev) throw new InvalidArgumentException('Identificador ou revisão inválida.');
        $plan=CommercialPlanCatalog::updateDraft($db,$adminId,$id,$rev,$changes);
        echo json_encode(['success'=>true,'plan'=>$plan],JSON_UNESCAPED_UNICODE);
    } finally {
        $db->close();
    }
} catch (InvalidArgumentException|DomainException $error) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>$error->getMessage()],JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Commercial plan admin: '.get_class($error));
    http_response_code(503);
    echo json_encode(['success'=>false,'message'=>'Não foi possível consultar ou atualizar planos.'],JSON_UNESCAPED_UNICODE);
}
