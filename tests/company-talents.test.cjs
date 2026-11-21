/** Contratos do Início ADM e diretório de talentos das empresas. */
const test = require('node:test'); // Detalhe DevIN: Carrega o executor de testes integrado do Node.js.
const assert = require('node:assert/strict'); // Detalhe DevIN: Importa asserções estritas.
const fs = require('node:fs'); // Detalhe DevIN: Permite ler arquivos versionados.
const path = require('node:path'); // Detalhe DevIN: Resolve caminhos do repositório.
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8'); // Detalhe DevIN: Consulta o código sem acessar produção.

test('ADM abre estatísticas no Início, com casinha antes de Empresas', () => { // Detalhe DevIN: Garante o comportamento de navegação solicitado.
  const adm = read('php/adm.php'); // Detalhe DevIN: Examina o dashboard administrativo.
  const start = adm.indexOf('href="adm.php?pagina=inicio"'); // Detalhe DevIN: Localiza o primeiro item do menu.
  const companies = adm.indexOf('href="adm.php?pagina=empresas"'); // Detalhe DevIN: Localiza a seção de empresas.
  assert.ok(start > 0 && companies > start); // Detalhe DevIN: Confirma a ordem principal.
  assert.match(adm, /dashboardIcon\('home'\).*<span class="menu-text">Início<\/span>/); // Detalhe DevIN: Garante casinha e título Início.
  assert.match(adm, /\$pagina === 'inicio'\): \?>/); // Detalhe DevIN: Confirma a inclusão das métricas no Início.
  assert.match(adm, /\$pagina === 'estatisticas'\) \$pagina = 'inicio'/); // Detalhe DevIN: Preserva acesso a links antigos.
  assert.match(read('php/components/admin-overview.php'), /admin-overview-shortcuts/); // Detalhe DevIN: Mantém atalhos administrativos.
}); // Detalhe DevIN: Encerra o contrato de navegação ADM.

test('Empresa mantém criação de vagas antes do catálogo de talentos', () => { // Detalhe DevIN: Protege a organização da página empresarial.
  const company = read('php/empresa.php'); // Detalhe DevIN: Inspeciona a página das empresas.
  const create = company.indexOf('class="create-job-panel"'); // Detalhe DevIN: Localiza publicação de vagas.
  const talents = company.indexOf("components/company-talents.php"); // Detalhe DevIN: Localiza catálogo de candidatos.
  assert.ok(create > 0 && talents > create); // Detalhe DevIN: Garante vagas acima dos candidatos.
  assert.match(company, /foreach \(\$empresaPosts as \$post\)/); // Detalhe DevIN: Preserva gerenciamento de vagas.
}); // Detalhe DevIN: Encerra teste da estrutura empresarial.

test('Talentos incluem fotos, busca paginada e navegação segura para chat e denúncia', () => { // Detalhe DevIN: Protege o escopo profissional dos perfis.
  const php = read('php/components/company-talents.php'); // Detalhe DevIN: Analisa fonte de dados e cartões.
  const js = read('js/company-talents.js'); // Detalhe DevIN: Analisa ações de perfil.
  assert.match(php, /FROM pessoa p LEFT JOIN curriculo/); // Detalhe DevIN: Usa todos os candidatos, não apenas inscritos.
  assert.match(php, /p\.foto/); // Detalhe DevIN: Seleciona fotos reais.
  assert.match(php, /profileAvatar/); // Detalhe DevIN: Utiliza o avatar existente com fallback.
  assert.match(php, /LIMIT \? OFFSET \?/); // Detalhe DevIN: Exige paginação.
  assert.match(php, /data-talent-open/); // Detalhe DevIN: Abre cartão profissional ao clicar.
  assert.match(php, /Reportar perfil/); // Detalhe DevIN: Mantém opção de denúncia.
  assert.match(js, /contato_tipo/); // Detalhe DevIN: Abre chat com alvo específico.
  assert.match(js, /reportar/); // Detalhe DevIN: Abre denúncia do chat.
  assert.doesNotMatch(js, /fetch\(|XMLHttpRequest/); // Detalhe DevIN: Impede novas APIs paralelas.
}); // Detalhe DevIN: Encerra contratos do catálogo.

test('Novo candidato vê vagas anteriores sem restrição pela data de cadastro', () => { // Detalhe DevIN: Garante catálogo histórico de oportunidades.
  const person = read('php/pessoa.php'); // Detalhe DevIN: Examina consulta de vagas do candidato.
  const begin = person.indexOf('FROM vagas v'); // Detalhe DevIN: Localiza busca global de vagas.
  const end = person.indexOf('ORDER BY v.id_vaga DESC', begin); // Detalhe DevIN: Localiza fim do SQL.
  assert.ok(begin >= 0 && end > begin); // Detalhe DevIN: Confirma consulta presente.
  assert.doesNotMatch(person.slice(begin, end), /p\.data_cadastro|p\.criado_em|data_registro|id_pessoa\s*=/i); // Detalhe DevIN: Não filtra pela criação do candidato.
}); // Detalhe DevIN: Encerra contrato de vagas anteriores.
