/** Contratos do Início ADM e diretório de talentos das empresas. */
const test = require('node:test'); // Explicação DevIN: Carrega o executor de testes integrado do Node.js.
const assert = require('node:assert/strict'); // Explicação DevIN: Importa asserções estritas.
const fs = require('node:fs'); // Explicação DevIN: Permite ler arquivos versionados.
const path = require('node:path'); // Explicação DevIN: Resolve caminhos do repositório.
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8'); // Explicação DevIN: Consulta o código sem acessar produção.

test('ADM abre estatísticas no Início, com casinha antes de Empresas', () => { // Explicação DevIN: Garante o comportamento de navegação solicitado.
  const adm = read('php/adm.php'); // Explicação DevIN: Examina o dashboard administrativo.
  const start = adm.indexOf('href="adm.php?pagina=inicio"'); // Explicação DevIN: Localiza o primeiro item do menu.
  const companies = adm.indexOf('href="adm.php?pagina=empresas"'); // Explicação DevIN: Localiza a seção de empresas.
  assert.ok(start > 0 && companies > start); // Explicação DevIN: Confirma a ordem principal.
  assert.match(adm, /dashboardIcon\('home'\).*<span class="menu-text">Início<\/span>/); // Explicação DevIN: Garante casinha e título Início.
  assert.match(adm, /\$pagina === 'inicio'\): \?>/); // Explicação DevIN: Confirma a inclusão das métricas no Início.
  assert.match(adm, /\$pagina === 'estatisticas'\) \$pagina = 'inicio'/); // Explicação DevIN: Preserva acesso a links antigos.
  assert.match(read('php/components/admin-overview.php'), /admin-overview-shortcuts/); // Explicação DevIN: Mantém atalhos administrativos.
}); // Explicação DevIN: Encerra o contrato de navegação ADM.

test('Empresa mantém criação de vagas antes do catálogo de talentos', () => { // Explicação DevIN: Protege a organização da página empresarial.
  const company = read('php/empresa.php'); // Explicação DevIN: Inspeciona a página das empresas.
  const create = company.indexOf('class="create-job-panel"'); // Explicação DevIN: Localiza publicação de vagas.
  const talents = company.indexOf("components/company-talents.php"); // Explicação DevIN: Localiza catálogo de candidatos.
  assert.ok(create > 0 && talents > create); // Explicação DevIN: Garante vagas acima dos candidatos.
  assert.match(company, /foreach \(\$empresaPosts as \$post\)/); // Explicação DevIN: Preserva gerenciamento de vagas.
}); // Explicação DevIN: Encerra teste da estrutura empresarial.

test('Talentos incluem fotos, busca paginada e navegação segura para chat e denúncia', () => { // Explicação DevIN: Protege o escopo profissional dos perfis.
  const php = read('php/components/company-talents.php'); // Explicação DevIN: Analisa fonte de dados e cartões.
  const js = read('js/company-talents.js'); // Explicação DevIN: Analisa ações de perfil.
  assert.match(php, /FROM pessoa p LEFT JOIN curriculo/); // Explicação DevIN: Usa todos os candidatos, não apenas inscritos.
  assert.match(php, /p\.foto/); // Explicação DevIN: Seleciona fotos reais.
  assert.match(php, /profileAvatar/); // Explicação DevIN: Utiliza o avatar existente com fallback.
  assert.match(php, /LIMIT \? OFFSET \?/); // Explicação DevIN: Exige paginação.
  assert.match(php, /data-talent-open/); // Explicação DevIN: Abre cartão profissional ao clicar.
  assert.match(php, /Reportar perfil/); // Explicação DevIN: Mantém opção de denúncia.
  assert.match(js, /contato_tipo/); // Explicação DevIN: Abre chat com alvo específico.
  assert.match(js, /reportar/); // Explicação DevIN: Abre denúncia do chat.
  assert.doesNotMatch(js, /fetch\(|XMLHttpRequest/); // Explicação DevIN: Impede novas APIs paralelas.
}); // Explicação DevIN: Encerra contratos do catálogo.

test('Novo candidato vê vagas anteriores sem restrição pela data de cadastro', () => { // Explicação DevIN: Garante catálogo histórico de oportunidades.
  const person = read('php/pessoa.php'); // Explicação DevIN: Examina consulta de vagas do candidato.
  const begin = person.indexOf('FROM vagas v'); // Explicação DevIN: Localiza busca global de vagas.
  const end = person.indexOf('ORDER BY v.id_vaga DESC', begin); // Explicação DevIN: Localiza fim do SQL.
  assert.ok(begin >= 0 && end > begin); // Explicação DevIN: Confirma consulta presente.
  assert.doesNotMatch(person.slice(begin, end), /p\.data_cadastro|p\.criado_em|data_registro|id_pessoa\s*=/i); // Explicação DevIN: Não filtra pela criação do candidato.
}); // Explicação DevIN: Encerra contrato de vagas anteriores.
