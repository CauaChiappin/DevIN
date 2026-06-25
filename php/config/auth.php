<?php

const JWT_SECRET = 'troque-esta-chave-devin-por-uma-chave-grande-e-secreta'; // chave secreta usada para assinar e verificar tokens JWT
const JWT_ISSUER = 'DevIN'; // nome do emissor do token JWT, usado para identificar a origem do token
const JWT_EXPIRATION_SECONDS = 3600; // tempo de expiração do token JWT em segundos (3600 segundos = 1 hora). Após esse tempo, o token não será mais válido e o usuário precisará fazer login novamente para obter um novo token.
const JWT_COOKIE_NAME = 'devin_token'; // nome do cookie que armazenará o token JWT. O cookie é usado para manter a sessão do usuário entre as requisições, permitindo que o usuário permaneça autenticado sem precisar enviar o token manualmente em cada requisição.
